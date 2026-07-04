<template>
  <div class="w-full">
    <div class="relative bg-white rounded-[1.75rem] shadow-lg border border-slate-200 min-h-[520px] p-6 sm:p-7 overflow-hidden">
      <div class="absolute left-6 top-6">
        <button
          type="button"
          class="text-slate-400 hover:text-slate-700 transition-colors"
          title="Back"
          @click="$emit('continue', { result: selectedResult })"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="absolute right-6 top-6" v-if="mode === 'browse'">
        <button
          type="button"
          class="text-indigo-300 hover:text-indigo-600 transition-colors"
          title="Flip card"
          @click="flipped = !flipped"
        >
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a4 4 0 0 1 4 4v1m0 0-3-3m3 3 3-3M21 14H11a4 4 0 0 1-4-4V9m0 0 3 3M7 9 4 12"></path>
          </svg>
        </button>
      </div>

      <div class="flex justify-center">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-100 px-4 py-2 text-sm font-bold text-emerald-800">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m5 14V9m5 10V3m5 16v-7"></path>
          </svg>
          <span>Mastery</span>
          <span class="text-emerald-700">{{ masteryLabel }}</span>
        </div>
      </div>

      <div class="mt-8 flex flex-col items-center text-center">
        <div class="text-sm font-semibold text-slate-500 mb-3">
          Card {{ currentIndex + 1 }} of {{ totalCards }}
        </div>

        <div class="w-full max-w-xs bg-slate-100 rounded-full h-2 overflow-hidden mb-10">
          <div
            class="h-full bg-emerald-500 rounded-full transition-all"
            :style="{ width: progressPercent + '%' }"
          ></div>
        </div>
      </div>

      <div v-if="!flipped" class="flex flex-col items-center justify-between min-h-[360px]">
        <div class="flex-1 flex items-center justify-center w-full">
          <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight text-slate-800 text-center max-w-xl" v-html="card.question"></h2>
        </div>

        <div class="w-full">
          <div class="flex items-center gap-4 mb-5 text-slate-400">
            <div class="h-px bg-slate-200 flex-1"></div>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104c.251-.023.503-.041.757-.054C12.472 2.95 14 4.496 14 6.447V9h.553C16.504 9 18.05 10.528 17.95 12.493a14.53 14.53 0 0 1-.054.757m-8.146-10.146C7.981 3.526 6.5 5.13 6.5 7v2.5H6A3.5 3.5 0 0 0 2.5 13v1.5A3.5 3.5 0 0 0 6 18h.5v-5.5m3.25-9.396L9.75 21m0-17.896C11.519 3.526 13 5.13 13 7v14"></path>
            </svg>
            <div class="h-px bg-slate-200 flex-1"></div>
          </div>

          <p class="text-center text-slate-500 font-medium mb-5">
            Think of your answer before revealing it. Choose an option below.
          </p>

          <div v-if="mode === 'review'" class="grid sm:grid-cols-2 gap-4">
            <button
              type="button"
              class="w-full rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg hover:shadow-xl transition-all p-5 text-left flex items-center gap-4"
              @click="chooseAnswer('know')"
            >
              <span class="w-10 h-10 rounded-full border-2 border-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
              </span>
              <span>
                <span class="block text-xl font-extrabold">I Know It</span>
                <span class="block text-xs font-medium text-emerald-50 mt-1">I can recall this</span>
              </span>
            </button>

            <button
              type="button"
              class="w-full rounded-2xl bg-red-500 hover:bg-red-600 text-white shadow-lg hover:shadow-xl transition-all p-5 text-left flex items-center gap-4"
              @click="chooseAnswer('notyet')"
            >
              <span class="w-10 h-10 rounded-full border-2 border-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </span>
              <span>
                <span class="block text-xl font-extrabold">Not Yet</span>
                <span class="block text-xs font-medium text-red-50 mt-1">I need another pass</span>
              </span>
            </button>
          </div>

          <div v-else class="grid grid-cols-2 gap-4">
            <button
              type="button"
              :disabled="!hasPrevious"
              class="rounded-2xl border border-slate-200 bg-white text-slate-600 font-bold py-4 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition"
              @click="$emit('previous')"
            >
              ← Previous
            </button>

            <button
              type="button"
              class="rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 transition"
              @click="flipped = true"
            >
              Show Answer
            </button>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col min-h-[360px]">
        <div v-if="mode === 'review'" class="rounded-2xl p-4 mb-8 flex items-center gap-4" :class="selectedResult === 'know' ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-red-50 text-red-800 border border-red-100'">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0" :class="selectedResult === 'know' ? 'bg-emerald-500' : 'bg-red-500'">
            <svg v-if="selectedResult === 'know'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="text-lg font-extrabold">
            You said: {{ selectedResult === 'know' ? 'I Know It' : 'Not Yet' }}
          </div>
        </div>

        <div class="flex-1 flex flex-col justify-center">
          <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-800 leading-tight mb-6 text-center" v-html="card.answer"></h3>

          <div v-if="card.hint" class="rounded-2xl bg-emerald-50 border border-emerald-100 p-5 text-left">
            <div class="flex items-center gap-3 text-emerald-800 font-extrabold mb-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-1.5 3-4.5 4.5-8 4.5 0 5.25 3.25 10.25 8 13.5 4.75-3.25 8-8.25 8-13.5-3.5 0-6.5-1.5-8-4.5z"></path>
              </svg>
              Why this matters
            </div>
            <div class="text-slate-600 leading-relaxed" v-html="card.hint"></div>
          </div>
        </div>

        <div v-if="mode === 'review'" class="mt-8">
          <button
            type="button"
            class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-xl py-5 shadow-lg hover:shadow-xl transition-all"
            @click="$emit('continue', { result: selectedResult })"
          >
            Continue →
          </button>
        </div>

        <div v-else class="mt-8 grid grid-cols-2 gap-4">
          <button
            type="button"
            class="rounded-2xl border border-slate-200 bg-white text-slate-600 font-bold py-4 hover:bg-slate-50 transition"
            @click="flipped = false"
          >
            Show Question
          </button>

          <button
            type="button"
            class="rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 transition"
            @click="$emit('next')"
          >
            Next →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  card: {
    type: Object,
    required: true,
  },
  mode: {
    type: String,
    default: 'review',
  },
  currentIndex: {
    type: Number,
    default: 0,
  },
  totalCards: {
    type: Number,
    default: 1,
  },
  masteryGoal: {
    type: Number,
    default: 4.0,
  },
  hasPrevious: {
    type: Boolean,
    default: false,
  },
  hasNext: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['answer', 'continue', 'previous', 'next']);

const flipped = ref(false);
const selectedResult = ref(null);

watch(
  () => props.card?.id,
  () => {
    flipped.value = false;
    selectedResult.value = null;
  }
);

const masteryScore = computed(() => {
  const value = Number(props.card?.masteryscore ?? 0);
  return Number.isNaN(value) ? 0 : value;
});

const masteryLabel = computed(() => masteryScore.value.toFixed(1));

const progressPercent = computed(() => {
  if (props.totalCards <= 0) {
    return 0;
  }

  return Math.min(100, Math.round(((props.currentIndex + 1) / props.totalCards) * 100));
});

const chooseAnswer = (result) => {
  selectedResult.value = result;
  emit('answer', {
    card: props.card,
    result,
  });
  flipped.value = true;
};
</script>
