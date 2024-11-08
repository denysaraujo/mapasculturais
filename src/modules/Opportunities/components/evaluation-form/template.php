<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    registration-evaluation-info
    evaluation-actions
');

/** @var MapasCulturais\Entities\Registration */
$entity = $this->controller->requestedEntity;

$opportunity = $entity->opportunity;
$evaluation_method_config_name = $opportunity->evaluationMethodConfiguration->name;
?>

<div class="registration__actions">
    <div ref=header>
        <h2 class="regular primary__color"><?= i::__("Formulário de") ?> <strong><?= $evaluation_method_config_name ?></strong></h2>
        <registration-evaluation-info :entity="entity"></registration-evaluation-info>
    </div>
    <div ref="form" style="overflow-y: auto;">
        <?php $this->part("{$entity->opportunity->evaluationMethod->slug}/evaluation-form"); ?>
    </div>

    <div ref="buttons">
        <evaluation-actions :form-data="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
    </div>
</div>