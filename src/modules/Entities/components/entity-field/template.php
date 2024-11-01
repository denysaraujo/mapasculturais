<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field-datepicker
    entity-field-links
    entity-field-location
    mc-alert
    mc-currency-input
    mc-multiselect
    mc-tag-list
    entity-field-bank-info
')
?>
<div v-if="propExists()" class="field" :class="[{error: hasErrors}, classes]" :data-field="prop">
    <label class="field__title" v-if="!hideLabel && !is('checkbox')" :for="propId">
        <slot>{{label || description.label}}</slot>
        <span v-if="description.required && !hideRequired" class="required">*<?php i::_e('obrigatório') ?></span>
        <slot name="info"></slot>
    </label>
    <slot name="input" >
        <?php //@todo implementar registro de tipos de campos (#1895) ?>

        <!-- masked fields -->
        <input v-if="is('cpf')" v-maska data-maska="###.###.###-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">

        <input v-if="is('cnpj')" v-maska data-maska="##.###.###/####-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">

        <input v-if="is('brPhone')" v-maska data-maska="['(##) #####-####','(##) ####-####']" data-maska-tokens="0:[0-9]:optional" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">
        <input v-if="is('cep')" v-maska data-maska="#####-###" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">
        <input v-if="is('fieldMask')" v-maska :data-maska="mask" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">

        <input v-if="is('string') || is('text')" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly" :readonly="readonly">

        <input v-if="is('integer') ||  is('number') ||  is('smallint')" :value="value" :id="propId" :name="prop" type="number" :min="min || description.min" :max="max || description.max" :step="description.step" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly" :readonly="readonly">

        <input v-if="is('email') || is('url')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly" :readonly="readonly">

        <input v-if="is('socialMedia')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly" :readonly="readonly">

        <entity-field-datepicker v-if="is('time') || is('datetime') || is('date')" :id="propId" :entity="entity" :prop="prop" :min-date="min" :max-date="max" :field-type="fieldType" @change="change($event, true)"></entity-field-datepicker>

        <textarea ref="textarea" v-if="is('textarea')" :value="value" :id="propId" :name="prop" :maxlength="maxLength" @input="change($event)" @blur="change($event,true)" :disabled="readonly" :readonly="readonly"></textarea>

        <select v-if="is('select')" :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)" :disabled="readonly || readonly">
            <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[optionValue]}}</option>
        </select>

        <template v-if="is('radio')">
            <label class="input__label input__radioLabel" v-for="(optionLabel, optionValue) in description.options">
                <input :checked="isRadioChecked(value, optionValue)" type="radio" :value="optionValue" @input="change($event,true)" @blur="change($event)" :disabled="readonly || readonly"> {{description.options[optionValue]}}
            </label>
        </template>

        <template v-if="is('links')">
            <entity-field-links :entity="entity" :prop="prop" :show-title="description && Boolean(description.registrationFieldConfiguration?.config?.title)" @change="change($event, true)"></entity-field-links>
        </template>

        <template v-if="is('multiselect') || is('checklist')">
           <div class="field__group">
                <template v-if="description.optionsOrder.length > 10">
                    <mc-multiselect @selected="change($event)" :model="selectedOptions[prop]" :items="description.optionsOrder" #default="{popover}" :max-options="maxOptions" hide-button>
                        <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle(); $event.preventDefault()" >
                            <?php i::_e("Selecionar") ?>
                            <mc-icon name="add"></mc-icon>
                        </button>
                    </mc-multiselect>

                    <mc-tag-list :tags="selectedOptions[prop]" classes="opportunity__background" @remove="change($event)" editable></mc-tag-list>
                </template>


                <label v-else class="input__label input__checkboxLabel input__multiselect" v-for="optionValue in description.optionsOrder">
                   <input :checked="value?.includes(optionValue)" type="checkbox" :value="optionValue" @change="change($event)" :disabled="readonly || readonly"> {{description.options[optionValue]}}
                </label>
            </div>
        </template>

        <template v-if="is('checkbox')">
            <div class="field__group">
                <label class="field__checkbox">
                    <input :id="propId" type="checkbox" :checked="value" @click="change($event)" :disabled="readonly || readonly"/>
                    <slot>{{label || description.label}}</slot>
                </label>
            </div>
        </template>

        <template v-if="is('boolean')">
            <select :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)" :disabled="readonly || readonly">
                <option :value='true' :selected="value"> <?= i::_e('Sim')?> </option>
                <option :value='false' :selected="!value"> <?= i::_e('Não')?>  </option>
            </select>
        </template>

        <template v-if="is('currency')">
            <mc-currency-input v-model="currencyValue" :entity="entity" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)"></mc-currency-input>
        </template>

        <template v-if="is('color')">
            <div class="field__color">
                <div class="field__color-input">
                    <input :value="value" :id="propId" :name="prop" type="color" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || readonly"/>
                </div>
            </div>
        </template>

        <template v-if="is('location')">
            <entity-field-location :entity="entity" :field-name="prop"></entity-field-location>
        </template>

        <template v-if="is('bankFields')">
            <entity-field-bank-info @change="change($event, true)"  :field-name="prop" :entity="entity"></entity-field-bank-info>
        </template>

        <div v-if="maxLength" class="field__length">{{ value ? value?.length : '0' }}/{{maxLength}}</div>
    </slot>

    <small class="field__description" v-if="!hideDescription && (fieldDescription || description.description)"> {{ fieldDescription || description.description}} </small>

    <small class="field__error" v-if="hasErrors">
        {{errors.join('; ')}}
    </small>
</div>