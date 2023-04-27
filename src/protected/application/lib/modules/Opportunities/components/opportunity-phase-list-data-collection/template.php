<?php
use MapasCulturais\i;

$this->import('
    confirm-button
    opportunity-phase-publish-date-config
')
?>

<mapas-card>
    <div class="grid-12 opportunity-phase-list-data-collection">
        <div v-if="entity.summary?.registrations" class="col-12">
            <h3><?php i::_e("Status das inscrições") ?></h3>
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong> <?= i::__('inscrições') ?></p>
            <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <?= i::__('inscrições') ?></p>
            <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <?= i::__('inscrições') ?></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <?= i::__('inscrições') ?></p>
        </div>
        <div class="col-12 opportunity-phase-list-data-collection_action--center">
            <mc-link :entity="entity" class="opportunity-phase-list-data-collection_action--button" icon="external" route="registrations" right-icon>
              <?= i::__("Lista de inscrições da fase") ?>
              <!-- Refatorar  -->
            </mc-link>
        </div>

        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div class="config-phase__line col-12"></div>
            <opportunity-phase-publish-date-config :phase="entity" :phases="phases" hide-datepicker></opportunity-phase-publish-date-config>
        </template>
    </div>
</mapas-card>