<?php
use MapasCulturais\i;
$this->import('mc-map-markercluster mc-map entities');
?>
<div class="home-map">
    <div class="home-map__content">
        <label class="home-map__content--title"><?php i::_e('Visualize também no mapa') ?></label>
        <p class="home-map__content--description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla. </p>
        <mc-map>
                <mc-map-markercluster v-for="entity in entities" :key="entity.__objectId" :entity="entity"></mc-map-markercluster>
        </mc-map>
    </div>
</div>