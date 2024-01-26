import '../css/addon.css';

import MinisetFieldtype from './components/fieldtypes/MinisetFieldtype.vue';
import MinisetClassesFieldtype from './components/fieldtypes/MinisetClassesFieldtype.vue';

Statamic.booting(() => {

    Statamic.$components.register('miniset-fieldtype', MinisetFieldtype);
    Statamic.$components.register('miniset_classes-fieldtype', MinisetClassesFieldtype);

});
