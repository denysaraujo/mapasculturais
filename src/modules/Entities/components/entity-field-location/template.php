<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    entity-map
    mc-select
');
?>

<div class="entity-field-location">
    <div class="col-12">
        <div class="grid-12">
            <div class="field col-12">
                <label for="cep">
                    <?= i::__('CEP') ?>
                </label>
                <input @change="pesquisacep(addressData.En_CEP, true);" id="cep" type="text" v-maska data-maska="#####-###" v-model="addressData.En_CEP" />
            </div>

            <div class="field col-4">
                <label for="logradouro">
                    <?= i::__('Logradouro') ?>
                </label>
                <input id="logradouro" type="text" v-model="addressData.En_Nome_Logradouro" @change="save" />
            </div>

            <div class="field col-4">
                <label for="num">
                    <?= i::__('Número') ?>
                </label>
                <input id="num" type="number" v-model="addressData.En_Num" @change="save" />
            </div>

            <div class="field col-4">
                <label for="bairro">
                    <?= i::__('Bairro') ?>
                </label>
                <input id="bairro" type="text" v-model="addressData.En_Bairro" @change="save" />
            </div>

            <div class="field col-12">
                <label for="complemento">
                    <?= i::__('Complemento') ?>
                </label>
                <input id="complemento" type="text" v-model="addressData.En_Complemento" @change="save" />
            </div>

            <div v-if="statesAndCitiesCountryCode != 'BR'" class="field">
                <label for="country">
                    <?= i::__('País') ?>
                </label>
                <input id="country" type="text" v-model="addressData.En_Pais" @change="save" />
            </div>

            <div class="field col-6">
                <label for="field__title">
                    <?= i::__('Estado') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="citiesList(); address()" v-model:default-value="addressData.En_Estado" show-filter>
                    <option v-for="state in states" :value="state.value">{{state.label}}</option>
                </mc-select>
            </div>
            
            <div class="field col-6">
                <label for="field__title">
                    <?= i::__('Cidade') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="address()" v-model:default-value="addressData.En_Municipio" show-filter>
                    <option v-for="city in cities" :value="city">{{city}}</option>
                </mc-select>
            </div>

            <div v-if="configs?.setPrivacy" class="field col-12">
                <label>
                    <input type="checkbox" v-model="addressData.publicLocation" @change="save()"/>
                    <?= i::__('Marque para deixar sua localização pública.') ?>
                </label>
            </div>
        </div>
    </div>
</div>