<template>
    <ModelTemplate v-slot="{ resolve, reject }">
        <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
            <div class="card w-96 bg-base-100 shadow-lg">
                <div class="card-body items-center text-center">
                    <TransitionGroup name="model-slides">
                        <!-- Payment Method Selection Slide | CURRENTLY NOT IN USE -->
                        <!-- <template v-if="currentStep == 'method'" key="method">
                            <h2 class="card-title">Select Payment method</h2>
                            <div class="join join-vertical lg:join-horizontal">
                                <input class="join-item btn" type="radio" name="options" aria-label="Cash"/>
                                <input class="join-item btn" type="radio" name="options" aria-label="Cheque"/>
                                <input class="join-item btn" type="radio" name="options" aria-label="Credit/Debit Card"/>
                            </div>
                        </template> -->

                        <!-- Paid amount and balance Amount -->
                        <template v-if="currentStep == 'amount'" key="amount">
                            <h2 class="card-title">Payment</h2>
                            <h3>Total : {{ amount }}</h3>
                            <div class="form-control w-full max-w-xs">
                                <label class="label">
                                    <span class="label-text">Paid amount</span>
                                </label>
                                <input v-model="payment" type="text" placeholder="Amount"
                                    class="input input-bordered w-full max-w-xs" />
                                <label class="label">
                                    <button @click="payment = amount?.toString()"
                                        class="btn btn-sm btn-neutral">Full</button>
                                    <button @click="payment = amount?.div(2).toString()"
                                        class="btn btn-sm btn-neutral">1/2</button>
                                </label>
                            </div>
                        </template>

                        <!-- Finalization, Show loading screen etc -->
                        <template v-if="currentStep == 'finalization'" key="finalization">
                            <h2 class="card-title">Confirm Payment</h2>

                        </template>
                    </TransitionGroup>
                    <div class="card-actions justify-end">
                        <TransitionGroup name="model-actions">
                            <button key="prev" v-if="previous" @click="goToPrevious"
                                class="btn btn-active btn-primary">Back</button>
                            <template v-if="!current.valid">
                                <div class="tooltip tooltip-right" data-tip="There is no data to be saved in the draft">
                                    <button class="btn btn-active btn-primary">{{ current.nextText }}</button>
                                </div>
                            </template>
                            <button key="next" @click="current.next(() => resolve({ payment: new Decimal(payment as string) }))" class="btn btn-active btn-primary">{{
                                current.nextText }}</button>
                        </TransitionGroup>
                    </div>
                </div>
            </div>
        </div>
    </ModelTemplate>
</template>

<script setup lang="ts">
import { createTemplatePromise, useStepper } from '@vueuse/core';
import Decimal from 'decimal.js';
import { isProperNumberString } from '../util/decimal-util'
import { ref, computed } from 'vue';

const { index, next, previous, goToNext, goToPrevious, stepNames, current } = useStepper({
    // 'method',
    'amount': {
        valid: computed(() => isProperNumberString(payment.value)),
        next: (resolver: () => void) => goToNext(),
        nextText: "Next"
    },
    // 'balance': {
    //     valid: true,
    //     next: () => goToNext()
    // },
    'finalization': {
        valid: true,
        next: (resolver: () => void) => resolver(),
        nextText: "Confirm"
    }
})

const currentStep = computed(() => stepNames.value[index.value]);

defineExpose({
    setup: (fullAmount: Decimal) => {
        amount.value = fullAmount
        return { start: ModelTemplate.start }
    }
});

const amount = ref<Decimal | undefined>();
const payment = ref<string | undefined>();

type ModelReturnType = {
    payment: Decimal
}

const ModelTemplate = createTemplatePromise<ModelReturnType>({
    transition: {
        name: 'model-content',
        appear: true,
    },
});
</script>

<style>
/** MODEL CONTENT TRANSITIONS */
.model-content-enter-active,
.model-content-leave-active {
    transition: opacity 0.3s ease;
}

.model-content-enter-from,
.model-content-leave-to {
    opacity: 0;
}

/** MODEL ACTION TRANSITION */

.model-actions-move,
.model-actions-leave-active {
    transition: all 0.3s ease;
}

.model-actions-enter-active {
    transition: all 0.3s ease;
    transition-delay: 0.3s;
}

.model-actions-enter-from,
.model-actions-leave-to {
    opacity: 0;
}

/** MODEL SLIDE TRANSITION */

.model-actions-move,
.model-actions-leave-active {
    transition: all 0.3s ease;
}

.model-actions-enter-active {
    transition: all 0.3s ease;
    transition-delay: 0.3s;
}

.model-actions-enter-from,
.model-actions-leave-to {
    opacity: 0;
}
</style>