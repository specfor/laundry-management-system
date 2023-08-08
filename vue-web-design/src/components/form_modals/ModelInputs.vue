<template>
    <div v-for="field in fields">
        <Select v-if="field['type'] === 'select'" :text="field['text']" :value="fieldValues[field['name']]"
            v-on:update:value="emitFieldValues" :disabled="field['disabled']" :name="field['name']"
            :options="field['options']" :errorMessage="errorMessages ? errorMessages[field['name']] : null">
        </Select>
        <CheckboxGroup v-else-if="field['type'] === 'checkbox'" :name="field['name']" :text="field['text']"
            :options="field['options']" v-on:update:options="emitFields" :errorMessage="errorMessages ? errorMessages[field['name']] : null"
            :disabled="field['disabled']"></CheckboxGroup>
        <TextArea v-else-if="field['type'] === 'textarea'" :text="field['text']" :name="field['name']"
            :errorMessage="errorMessages ? errorMessages[field['name']] : null" 
            :value="fieldValues[field['name']]" v-on:update:value="emitFieldValues"
            :disabled="field['disabled']"></TextArea>
        <Message v-else-if="field['type'] === 'message'" :text="field['text']"></Message>
        <Heading v-else-if="field['type'] === 'heading'" :text="field['text']"></Heading>
        <CustomizedInput v-else :text="field['text']" :errorMessage="errorMessages ? errorMessages[field['name']] : null">
            <input class="col-span-2 border-2 border-slate-400 rounded-md px-3 hover:border-slate-700
                         py-0.5 hover:bg-slate-100 focus:bg-slate-200 disabled:border" :disabled="field['disabled']"
                :type="field['type']" :value="fieldValues[field['name']]" :min="field['min']" :max="field['max']"
                v-model="fieldValues[field['name']]" @input="emitFieldValues">
        </CustomizedInput>
    </div>
</template>

<script setup>
import Select from "../input-components/Select.vue";
import CheckboxGroup from "../input-components/Checkbox.vue";
import TextArea from "../input-components/TextArea.vue";
import Message from "../input-components/Message.vue";
import Heading from "../input-components/Heading.vue";
import CustomizedInput from "../input-components/CustomizedInput.vue";

const { errorMessages, ...props } = defineProps(['errorMessages', 'fieldValues', 'fields']);

const fieldValues = ref(props.fieldValues);
const fields = ref(props.fields);

const emitFieldValues = (newVal) => this.$emit('fieldValue:update', newVal);
const emitFields = (newVal) => this.$emit('fields:update', newVal);
</script>
