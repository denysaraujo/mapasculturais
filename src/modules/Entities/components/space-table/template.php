<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-icon
    mc-export-spreadsheet
');
?>

<div class="space-table">
    <entity-table type="space" identifier="spaceTable" :query="query" :limit="100" :headers="headers" endpoint="find" required="name,type" :visible="visibleColumns" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filter="hideFilters" show-index>
        <template #actions="{entities}">
            <div class="space-table__actions">
                <h4 class="bold"><?= i::__('Ações:') ?></h4>
                <mc-export-spreadsheet :owner="owner" endpoint="entities" :params="{entityType: 'space', query}" group="entities-spreadsheets"></mc-export-spreadsheet>
            </div>
        </template>

        <template #filters="{entities,filters}">
            <div class="space-table__multiselects">
                <mc-multiselect class="col-2" :model="selectedType" :items="types" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>" @selected="filterByType(entities)" @removed="filterByType(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

                <mc-multiselect class="col-2" :model="selectedArea" :items="terms" placeholder="<?php i::_e('Selecione as áreas de interesse') ?>" @selected="filterByArea(entities)" @removed="filterByArea(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>
        
                <mc-multiselect class="col-2" :model="selectedSeals" :items="seals" placeholder="<?php i::_e('Selecione os selos') ?>" @selected="filterBySeals(entities)" @removed="filterBySeals(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>
            </div>
            <div class="space-table__inputs">
                <div class="field--horizontal">
                    <label><input @click="filterByAccessibility($event,entities)" ref="acessibility" type="checkbox" name="spaceType"> <?php i::_e('Possui acessibilidade') ?> </label>
                    <label class="verified"><input v-model="verified" @change="getVerified()" :true-value="1" :false-value="undefined" type="checkbox"> <?php i::_e('Espaços oficiais') ?><mc-icon name="circle-checked"></mc-icon> </label>
                </div>
            </div>
        </template>
    </entity-table>
</div>