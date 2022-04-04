import MinisetClassesFieldtype from './components/Fieldtypes/MinisetClassesFieldtype.vue';
import MinisetOptionsFieldtype from './components/Fieldtypes/MinisetOptionsFieldtype.vue';

Statamic.booting(() => {

    Statamic.$components.register('miniset_classes-fieldtype', MinisetClassesFieldtype);
    Statamic.$components.register('miniset_options-fieldtype', MinisetOptionsFieldtype);

});
