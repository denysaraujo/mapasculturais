<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-popover
    mc-tag-list
');
?>

<div>
    <mc-popover openside="down-right">
        <template #button="popover">
            <button @click="popover.toggle()" class="button button--primary-outline button--sm button--icon">
                <mc-icon name="add"></mc-icon>
                <?php i::_e("Adicionar") ?>
            </button>
        </template>

        <template #default="{close}">
            <form @submit="addConfig(); $event.preventDefault(); close();">
                <div class="grid-12">
                    <div class="related-input col-6">
                        <select v-model="selectedField">
                            <option v-if="registrationCategories.length > 0" value="category" :disabled="isFieldExcluded('category')"><?php i::_e("Categoria") ?></option>
                            <option v-if="registrationProponentTypes.length > 0" value="proponentType" :disabled="isFieldExcluded('proponentType')"><?php i::_e("Tipos do proponente") ?></option>
                            <option v-if="registrationRanges.length > 0" value="range" :disabled="isFieldExcluded('range')"><?php i::_e("Faixa/Linha") ?></option>
                            <option value="distribution" :disabled="isGlobal"><?php i::_e("Distribuição") ?></option>
                            <option v-if="Object.keys(registrationSelectionFields).length > 0" v-for="(options, title) in registrationSelectionFields" :key="title" :value="title" :disabled="isFieldExcluded(title)">{{ title }}</option>
                        </select>
                    </div>

                    <div v-if="selectedField == 'category'" class="related-input col-6">
                        <select v-model="selectedConfigs" multiple>
                            <option v-for="category in filteredFields.categories" :key="category" :value="category">
                                {{ category }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="selectedField == 'proponentType'" class="related-input col-6">
                        <select v-model="selectedConfigs" multiple>
                            <option v-for="proponentType in filteredFields.proponentTypes" :key="proponentType" :value="proponentType">
                                {{ proponentType }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="selectedField == 'range'" class="related-input col-6">
                        <select v-model="selectedConfigs" multiple>
                            <option v-for="range in filteredFields.ranges" :key="range" :value="range">
                                {{ range }}
                            </option>
                        </select>
                    </div>

                    <div v-if="selectedField == 'distribution'" class="related-input col-6">
                        <input type="text" placeholder="00-99" maxlength="5" v-model="selectedDistribution"/>
                    </div>
                    
                    <div v-if="registrationSelectionFields[selectedField] && registrationSelectionFields[selectedField].length > 0" class="related-input col-6">
                        <select v-model="selectedConfigs" multiple>
                            <option v-for="option in registrationSelectionFields[selectedField]" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>

    <mc-tag-list :tags="fillTagsList" @remove="removeTag" editable></mc-tag-list>
</div>
