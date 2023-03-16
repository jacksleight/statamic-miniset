/* @deprecated: Alias the Statamic v4 scales so the same classes can work in v3 as well, drop when v3 is dropped */
import '../css/addon.css';
import '../css/jstw.css';

import MinisetFieldtype from './components/Fieldtypes/MinisetFieldtype.vue';
import MinisetClassesFieldtype from './components/Fieldtypes/MinisetClassesFieldtype.vue';

Statamic.booting(() => {

    Statamic.$components.register('miniset-fieldtype', MinisetFieldtype);
    Statamic.$components.register('miniset_classes-fieldtype', MinisetClassesFieldtype);

});
