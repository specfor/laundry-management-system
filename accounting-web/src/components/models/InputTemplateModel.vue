<template>
    <ModelTemplate v-slot="{ resolve, reject }">
        <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
            <div class="card w-2/3 bg-white shadow-lg">
                <div class="card-body items-center text-center">
                    <h2 class="card-title">{{ _header }}</h2>
                    <slot name="inputs" :setValue="setValue" :mergeValue="mergeValue" :value="_data" :data="_passToSlot" :errors="errorMessages">
                    </slot>
                    <!-- <input v-model="inputRef" type="text" placeholder="Draft Name"
                        class="input input-bordered input-sm w-full max-w-xs" /> -->
                    <div class="card-actions justify-end">
                        <div class="tooltip" v-if="errorsExist" data-tip="Please resolve the errors">
                            <button class="btn btn-primary" disabled>{{ _confirmActionText }}</button>
                        </div>
                        <button v-else class="btn btn-primary" @click="handleResolve(resolve)">{{
                            _confirmActionText }}</button>
                        <button class="btn btn-ghost" @click="reject({})">{{ _cancelActionText }}</button>
                    </div>
                </div>
            </div>
        </div>
    </ModelTemplate>

    <!-- Loading Model -->
    <Teleport to="body">
        <template v-if="isLoadingModelVisible">
            <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
                <form method="dialog" class="modal-box flex flex-row justify-center w-auto">
                    <span class="loading loading-spinner loading-lg"></span>
                    <div class="ml-5 prose">
                        <h3 class="font-bold text-xl p-0 m-0">{{ loadingModelHeader }}</h3>
                        <p>Please wait .... </p>
                    </div>
                </form>
            </div>
        </template>
    </Teleport>
</template>

<script setup lang="ts" generic="PassToSlot, V extends object">
import { createTemplatePromise, useConfirmDialog } from '@vueuse/core';
import { type Ref, ref, computed, watch } from 'vue';
import { isEqual } from 'lodash'
import type { AllObjectPropsTo } from '../../types';
import type { Compulsory } from 'ts-toolbelt/out/Object/Compulsory';

const { initialValue, validator } = defineProps<{
    initialValue: V
    validator: (data: V, extraData: PassToSlot) => AllObjectPropsTo<V, string | undefined>
}>()

defineExpose({
    setup: (header: string, confirmActionText?: string, cancelActionText?: string, passToSlot?: PassToSlot) => {
        _data.value = initialValue;
        _header.value = header;
        confirmActionText ? _confirmActionText.value = confirmActionText : null;
        cancelActionText ? _cancelActionText.value = cancelActionText : null;
        passToSlot ? _passToSlot.value = passToSlot : null;

        const showLoading = (header?: string) => {
            header ? loadingModelHeader.value = header : null;
            revealLoadingModel()
        }

        return { start: ModelTemplate.start, showLoading, finish: hideLoadingModel }
    }
});

/** For the value of the input */
const _data = ref<V>(initialValue) as Ref<V>;
const _passToSlot = ref<PassToSlot>();
const _header = ref("");
const _confirmActionText = ref("Confirm")
const _cancelActionText = ref("Cancel")

// For the Loading model after clicking save records
const { isRevealed: isLoadingModelVisible, reveal: revealLoadingModel, cancel: hideLoadingModel } = useConfirmDialog()
const loadingModelHeader = ref("Performing Action");

// passToSlot will most likely be loaded before the model is shown, and before the first validator call
const errorMessages = computed(() => !isEqual(_data.value, {}) && !!(_passToSlot.value) ? validator(_data.value, _passToSlot.value as PassToSlot) : {} as AllObjectPropsTo<V, string>)

const errorsExist = computed(() => (Object.keys(errorMessages.value) as Array<keyof AllObjectPropsTo<V, string | undefined>>).filter(key => errorMessages.value[key] != undefined).length > 0)

type ModelReturnType = {
    action: "cancel" | "confirm",
    data?: Compulsory<V>
}

const ModelTemplate = createTemplatePromise<ModelReturnType>({
    transition: {
        name: 'model-content',
        appear: true,
    },
});

const setValue = (value: V) => _data.value = value;

const mergeValue = (value: string, key: keyof V) => _data.value = {..._data.value, [key]: value};

const handleResolve = (resolve: (v: ModelReturnType | Promise<ModelReturnType>) => void) => !isEqual(initialValue, _data) ? resolve({ action: 'confirm', data: _data.value as Compulsory<V> }) : null
</script>

<style>
.model-content-enter-active,
.model-content-leave-active {
    transition: opacity 0.5s ease;
}

.model-content-enter-from,
.model-content-leave-to {
    opacity: 0;
}
</style>