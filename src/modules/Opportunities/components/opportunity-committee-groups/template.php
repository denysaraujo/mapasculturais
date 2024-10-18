<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-icon
    mc-popover
    mc-tab
    mc-tabs
    mc-toggle
    opportunity-evaluation-committee
    opportunity-registration-filter-configuration
');
?>

<div class="opportunity-committee-groups">
    <div class="opportunity-committee-groups__description">
       <p><?php i::_e('Defina os agentes que farão parte das comissões de avaliação desta fase.') ?></p>
    </div>

    <mc-tabs>
        <template #after-tablist>
            <button v-if="hasTwoOrMoreGroups && entity.useCommitteeGroups" class="button button--icon button--primary button--sm" @click="addGroup(minervaGroup, true);">
                <mc-icon name="add"></mc-icon>
                <?php i::_e('Voto de minerva') ?>
            </button>
            
            <mc-popover openside="down-right">
                <template #button="popover">
                    <button @click="popover.toggle()" class="button button--primary-outline button--sm button--icon">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Adicionar comissão") ?>
                    </button>
                </template>

                <template #default="{close}">
                    <form @submit="addGroup(newGroupName); $event.preventDefault(); close();">
                        <div class="grid-12">
                            <div class="related-input col-12">
                                <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome da comissão') ?>" maxlength="64" />
                            </div>
                            <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                            <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </template>
            </mc-popover>
        </template>

        <mc-tab v-for="(relations, groupName) in groups" :key="groupName" :label="groupName" :slug="groupName">
            <div class="opportunity-committee-groups__group">
                <div class="opportunity-committee-groups__edit-group field">
                    <label for="newGroupName"><?= i::__('Título da comissão') ?></label>

                    <div class="opportunity-committee-groups__edit-group--field">
                        <input id="newGroupName" v-model="relations.newGroupName" class="input" type="text" @input="updateGroupName(groupName, relations.newGroupName)" @blur="saveGroupName(groupName)" placeholder="<?= i::esc_attr__('Digite o novo nome do grupo') ?>" />
                        <mc-confirm-button @confirm="removeGroup(groupName)">
                            <template #button="modal">
                                <a class="button button--delete button--icon button--sm" @click="modal.open()">
                                    <mc-icon name="trash"></mc-icon>
                                    <?= i::__('Excluir comissão') ?> 
                                </a>
                            </template>
                            <template #message="message">
                                <?php i::_e('Remover comissão de avaliadores?') ?>
                            </template>
                        </mc-confirm-button>
                    </div> 
                </div>

                <div class="opportunity-committee-groups__multiple-evaluators">
                    <div class="field">
                        <mc-toggle
                            :modelValue="entity?.registrationFilterConfig[groupName] && Object.keys(entity.registrationFilterConfig[groupName]).length > 0" 
                            @update:modelValue="enableRegisterFilterConf($event, groupName)"
                            label="<?= i::__('Configuração filtro de inscrição para avaliadores/comissão') ?>"
                        />
                        <opportunity-registration-filter-configuration 
                            v-if="entity?.registrationFilterConfig[groupName]" 
                            :entity="entity"
                            v-model:default-value="entity.registrationFilterConfig[groupName]"
                            :excludeFields="globalExcludeFields"
                            @updateExcludeFields="updateExcludedFields('global', $event)"
                            is-global
                        >
                        </opportunity-registration-filter-configuration>
                    </div>


                    <div class="field">
                        <mc-toggle
                            :modelValue="localSubmissionEvaluatorCount[groupName] > 0" 
                            @update:modelValue="changeMultipleEvaluators($event, groupName)"
                            label="<?= i::__('Limitar número de avaliadores por inscrição') ?>"
                        />
                        <div v-if="localSubmissionEvaluatorCount[groupName]" class="field">
                            <input v-model="localSubmissionEvaluatorCount[groupName]" type="number" @change="autoSave()"/>
                        </div>
                    </div>
    
                </div>
                
                <div class="opportunity-committee-groups__evaluators">
                    <opportunity-evaluation-committee :entity="entity" :group="groupName" :excludeFields="individualExcludeFields" @updateExcludeFields="updateExcludedFields('individual', $event)"></opportunity-evaluation-committee>
                </div>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
