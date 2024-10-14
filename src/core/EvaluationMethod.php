<?php
namespace MapasCulturais;

use DateTime;
use \MapasCulturais\i;

abstract class EvaluationMethod extends Module implements \JsonSerializable{
    abstract protected function _register();

    abstract function enqueueScriptsAndStyles();

    abstract function getSlug();
    abstract function getName();
    abstract function getDescription();
    

    abstract protected function _getConsolidatedResult(Entities\Registration $registration);
    abstract function getEvaluationResult(Entities\RegistrationEvaluation $evaluation);

    abstract function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): ?array;
    abstract function _getConsolidatedDetails(Entities\Registration $registration): ?array;

    abstract function valueToString($value);

    public function cmpValues($value1, $value2){
        if($value1 > $value2){
            return 1;
        } elseif($value1 < $value2){
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * Filtra o resultado do sumário da fase de avaliação
     * 
     * @param array $data 
     * @return array 
     */
    public function filterEvaluationsSummary(array $data) {
        return $data;
    }

    /**
     * @param Entities\RegistrationEvaluation $evaluation
     *
     * @return array of errors
     */
    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data){
        return [];
    }

    function getReportConfiguration($opportunity, $call_hooks = true){
        $app = App::i();

        // Registration Section Columns
        $registration_columns = [];
        if($opportunity->projectName){
            $registration_columns['projectName'] = (object) [
                'label' => i::__('Nome do projeto'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->projectName;
                }
            ];
        }

        if($opportunity->registrationCategories){
            $registration_columns['category'] = (object) [
                'label' => i::__('Categoria de inscrição'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->category;
                }
            ];
        }

        $registration_columns = $registration_columns + [
            'owner' => (object) [
                'label' => i::__('Agente Responsável'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->owner->name;
                }
            ],
            'number' => (object) [
                'label' => i::__('Número de inscrição'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation){
                    return $evaluation->registration->number;
                }
            ],
        ];


        /*
         * @TODO: adicionar as colunas abaixo:
         * - tempo de permanência na avaliacao
         */
        $committee_columns = [
            'evaluator' => (object) [
                'label' => i::__('Nome'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->user->profile->name;
                }
            ]
        ];


        $evaluation_columns = [
            'result' => (object) [
                'label' => i::__('Resultado'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->getResultString();
                }
            ],
            'status' => (object) [
                'label' => i::__('Status'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $evaluation->getStatusString();
                }
            ],
        ];

        $sections = [
            'registration' => (object) [
                'label' => i::__('Informações sobre as inscrições e proponentes'),
                'color' => '#CCCCFF',
                'columns' => $registration_columns
            ],

            'committee' => (object) [
                'label' => i::__('Informações sobre o avaliador'),
                'color' => '#CCFFCC',
                'columns' => $committee_columns
            ],

            'evaluation' => (object) [
                'label' => i::__('Avaliação'),
                'color' => '#00AA00',
                'columns' => $evaluation_columns
            ]
        ];

        if($call_hooks){
            $app->applyHookBoundTo($this, "evaluationsReport({$this->slug}).sections", [$opportunity, &$sections]);

            foreach($sections as $section_slug => &$section){
                $app->applyHookBoundTo($this, "evaluationsReport({$this->slug}).section({$section_slug})", [$opportunity, &$section]);
            }
        }

        return $sections;
    }


    function evaluationToString(Entities\RegistrationEvaluation $evaluation){
        return $this->valueToString($evaluation->result);
    }
    
    function fetchRegistrations(){
        return false;
    }

    function getConsolidatedResult(Entities\Registration $registration){
        $registration->checkPermission('viewConsolidatedResult');

        return $this->_getConsolidatedResult($registration);
    }

    function getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): array {
        $app = App::i();
        $result = $this->_getEvaluationDetails($evaluation);
        $app->applyHookBoundTo($evaluation, "{$evaluation->hookPrefix}.details", [&$result]);
        return $result;
    }

    function getConsolidatedDetails(Entities\Registration $registration): array {
        $app = App::i();
        $result = $this->_getConsolidatedDetails($registration);
        $result['sentEvaluationCount'] = count($registration->sentEvaluations);

        $app->applyHookBoundTo($registration, "{$registration->hookPrefix}.details", [&$result]);
        return $result;
    }

    private $_canUserEvaluateRegistrationCache = [];

    public function canUserEvaluateRegistration(Entities\Registration $registration, $user, $skip_exceptions = false){
        if($user->is('guest')){
            return false;
        }
        $cache_id = "$registration -> $user";

        if(!$skip_exceptions && isset($this->_canUserEvaluateRegistrationCache[$cache_id])){
            return $this->_canUserEvaluateRegistrationCache[$cache_id];
        }

        $config = $registration->getEvaluationMethodConfiguration();

        if (
            empty($config->fetch->{$user->id}) 
            && empty($config->fetchCategories->{$user->id}) 
            && empty($config->fetchRanges->{$user->id})
            && empty($config->fetchProponentTypes->{$user->id})
        ) {
            return false;
        };
        
        $can = $config->canUser('@control', $user);
        
        if($can && $this->fetchRegistrations()){
            
            $fetch = [];
            $config_fetch = is_array($config->fetch) ? $config->fetch : (array) $config->fetch;
            $config_fetchCategories = is_array($config->fetchCategories) ? $config->fetchCategories : (array) $config->fetchCategories;
            $config_ranges = is_array($config->fetchRanges) ? $config->fetchRanges : (array) $config->fetchRanges;
            $config_proponent_types = is_array($config->fetchProponentTypes) ? $config->fetchProponentTypes : (array) $config->fetchProponentTypes;

            if(is_array($config_fetch)){
                foreach($config_fetch as $id => $val){
                    $fetch [(int)$id] = $val;
                }
            }
            $fetch_categories = [];
            if(is_array($config_fetchCategories)){
                foreach($config_fetchCategories as $id => $val){
                    $fetch_categories [(int)$id] = $val;
                }
            }

            $fetch_ranges = [];
            if(is_array($config_ranges)){
                foreach($config_ranges as $id => $val){
                    $fetch_ranges [(int)$id] = $val;
                }
            }

            $fetch_proponent_types = [];
            if(is_array($config_proponent_types)){
                foreach($config_proponent_types as $id => $val){
                    $fetch_proponent_types [(int)$id] = $val;
                }
            }

            if(isset($fetch[$user->id])){
                $ufetch = $fetch[$user->id];
                if(preg_match("#([0-9]+) *[-] *([0-9]+)*#", $ufetch, $matches)){
                    $s1 = $matches[1];
                    $s2 = $matches[2];
                    
                    $len = max([strlen($s1), strlen($s2)]);
                    
                    $fin = substr($registration->number, -$len);
                    
                    if(intval($s2) == 0){ // "00" => "100"
                        $s2 = "1$s2";
                    }
                    if($fin < $s1 || $fin > $s2){
                        $can = false;
                    }
                }
            }

            if(isset($fetch_categories[$user->id])){
                $ucategories = $fetch_categories[$user->id];
                if($ucategories){
                    if(!is_array($ucategories)) {
                        $ucategories = explode(';', $ucategories);
                    }

                    if($ucategories){
                        $found = false;

                        foreach($ucategories as $cat){
                            $cat = trim($cat);
                            if(strtolower((string)$registration->category) === strtolower($cat)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }

            if(isset($fetch_ranges[$user->id])){
                $uranges = $fetch_ranges[$user->id];
                if($uranges){
                    if(!is_array($uranges)) {
                        $uranges = explode(';', $uranges);
                    }

                    if($uranges){
                        $found = false;

                        foreach($uranges as $ran){
                            $ran = trim($ran);
                            if(strtolower((string)$registration->range) === strtolower($ran)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }
            
            if(isset($fetch_proponent_types[$user->id])){
                $uproponet_types = $fetch_proponent_types[$user->id];
                if($uproponet_types){
                    if(!is_array($uproponet_types)) {
                        $uproponet_types = explode(';', $uproponet_types);
                    }

                    if($uproponet_types){
                        $found = false;

                        foreach($uproponet_types as $ran){
                            $ran = trim($ran);
                            if(strtolower((string)$registration->proponentType) === strtolower($ran)){
                                $found = true;
                            }
                        }

                        if(!$found) {
                            $can = false;
                        }
                    }
                }
            }
        }

        if(!$skip_exceptions) {
            $can = $can || in_array($user->id, $registration->valuersIncludeList);
            $can = $can && !in_array($user->id, $registration->valuersExcludeList);
            $this->_canUserEvaluateRegistrationCache[$cache_id] = $can;
        }
        
        return $can;
    }

    function canUserViewConsolidatedResult(Entities\Registration $registration){
        $opp = $registration->opportunity;

        if($opp->publishedRegistrations || $opp->canUser('@control')){
            return true;
        } else {
            return false;
        }
    }

    function getEvaluationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-form";
    }

    public function getEvaluationSummary($registration) {
        $app = App::i();

        $result = [];
        if($evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration])){
            $consolidated_result =  $this->_getConsolidatedResult($registration);
            $result['consolidated_result'] = $consolidated_result;
            $result['type'] = $this->getName();
            $result['value_to_string'] = $this->valueToString($consolidated_result);

            foreach($evaluations as $evaluation){
                $data = [
                    'id' => $evaluation->id,
                    'evaluation_data' => $evaluation->evaluationData,
                    'avaluator_id' => $evaluation->user->profile->id,
                    'avaluator_name' => $evaluation->user->profile->name,
                    'status' => $evaluation->getResultString(),
                ];

                $result[] = (object)$data;
            }
        }

        return $result;
    }

    function getEvaluationViewPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-view";
    }

    function getEvaluationFormInfoPartName(){
        $slug = $this->getSlug();

        return "$slug--evaluation-info";
    }
    
    function getConfigurationFormPartName(){
        $slug = $this->getSlug();

        return "$slug--configuration-form";
    }

    function register(){
        $app = App::i();

        $def = new Definitions\EvaluationMethod($this);

        $app->registerEvaluationMethod($def);

        $type = new Definitions\EntityType('MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug(), $this->getName());

        $app->registerEntityType($type);

        $this->_register();

        $self = $this;

        $app->hook('view.includeAngularEntityAssets:after', function() use($self){
            $self->enqueueScriptsAndStyles();
        });
        
        if($this->fetchRegistrations()){
            $this->registerEvaluationMethodConfigurationMetadata('fetch', [
                'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores'),
                'serialize' => function ($val) {
                    return json_encode($val);
                },
                'unserialize' => function($val) {
                    return json_decode((string) $val);
                }
            ]);
            $this->registerEvaluationMethodConfigurationMetadata('fetchCategories', [
                'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por categoria'),
                'serialize' => function ($val) {
                    return json_encode($val);
                },
                'unserialize' => function($val) {
                    return json_decode((string) $val);
                }
            ]);

            $this->registerEvaluationMethodConfigurationMetadata('fetchRanges', [
                'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por faixa'),
                'serialize' => function ($val) {
                    return json_encode($val);
                },
                'unserialize' => function($val) {
                    return json_decode((string) $val);
                }
            ]);

            $this->registerEvaluationMethodConfigurationMetadata('fetchProponentTypes', [
                'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por tipo de proponente'),
                'serialize' => function ($val) {
                    return json_encode($val);
                },
                'unserialize' => function($val) {
                    return json_decode((string) $val);
                }
            ]);

            $this->registerEvaluationMethodConfigurationMetadata('infos', [
                'label' => i::__('Textos informativos para os avaliadores'),
                'type' => 'json',
                'default' => '{}'
            ]);
        }

    }
    
    function registerEvaluationMethodConfigurationMetadata($key, array $config){
        $app = App::i();

        $metadata = new Definitions\Metadata($key, $config);

        $app->registerMetadata($metadata, 'MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug());
    }

    function usesEvaluationCommittee(){
        return true;
    }

    public function jsonSerialize(): array {
        return [];
    }
}
