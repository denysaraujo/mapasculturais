<?php
use MapasCulturais\i;
?>
<span v-if="condition || entity?.__processing" class="loading">
    <iconify icon="eos-icons:loading"></iconify> {{entity?.__processing || '<?php i::_e('carregando...') ?>'}}
</span>