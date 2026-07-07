<template>
  <div class="relative w-full max-w-md mx-auto aspect-[3/4] flip-container group cursor-pointer" :class="{ 'flipped': isFlipped }" @click="toggleFlip">
    <div class="flip-card-inner w-full h-full absolute top-0 left-0 transition-transform duration-700 rounded-3xl">
      
      <!-- Front -->
      <div class="flip-card-front absolute w-full h-full bg-white rounded-3xl p-6 flex flex-col justify-center items-center border-[2px] border-slate-100 border-b-[8px] bg-clip-padding shadow-lg hover:shadow-xl transition-shadow bg-gradient-to-b from-white to-slate-50">
        <div class="flex-grow flex flex-col justify-center items-center overflow-y-auto w-full">
          <h3 class="text-2xl font-bold text-slate-800 text-center" v-html="card.question"></h3>
          
          <div v-if="card.hint" class="mt-8 w-full flex flex-col items-center" @click.stop>
            <button v-if="!showHint" @click="showHint = true" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium py-2 px-5 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-colors flex items-center gap-2 border border-transparent hover:border-indigo-200">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
              {{ getString('showhint') }}
            </button>
            <div v-else class="bg-indigo-50 text-indigo-800 p-4 rounded-xl text-sm italic w-full text-center border border-indigo-100 shadow-inner">
              <span class="block font-bold text-xs uppercase tracking-wider text-indigo-600 mb-1">{{ getString('hint') }}</span>
              <span v-html="card.hint"></span>
            </div>
          </div>
        </div>

        <p class="text-sm text-slate-400 mt-6 flex justify-center items-center gap-2 font-medium bg-slate-50 px-4 py-1.5 rounded-full border border-slate-200">
            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
            {{ getString('taptoflip') }}
        </p>
      </div>

      <!-- Back -->
      <div class="flip-card-back absolute w-full h-full bg-white/95 backdrop-blur-sm rounded-3xl p-6 flex flex-col border border-indigo-100 shadow-2xl shadow-indigo-100/50">
        <div class="absolute top-4 right-4 text-indigo-300">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
        </div>
        
        <div class="flex-grow flex flex-col justify-center items-center overflow-y-auto mt-4 px-2">
          <div class="text-xl font-medium text-slate-700 mb-6 text-center leading-relaxed" v-html="card.answer"></div>
        </div>
        
        <!-- Controls -->
        <div class="flex flex-col gap-2 mt-auto" @click.stop v-if="isFlipped">
          <div class="flex justify-between gap-2">
            <button @click="$emit('rate', 0)" class="flex-1 bg-red-50 hover:bg-red-100 text-red-700 font-bold py-3 rounded-xl transition-all active:scale-95 duration-200 border border-red-100 hover:border-red-200 flex flex-col items-center justify-center">
              <span>{{ getString('hard_btn') }}</span>
              <span class="block text-[9px] font-medium opacity-70 mt-0.5 uppercase tracking-wide">🔴 {{ getString('action_back') }}</span>
            </button>
            <button @click="$emit('rate', 1)" class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold py-3 rounded-xl transition-all active:scale-95 duration-200 border border-amber-200 hover:border-amber-300 flex flex-col items-center justify-center">
              <span>{{ getString('again_btn') }}</span>
              <span class="block text-[9px] font-medium opacity-70 mt-0.5 uppercase tracking-wide">🟠 {{ getString('action_stay') }}</span>
            </button>
          </div>
          <button @click="$emit('rate', 2)" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl transition-all active:scale-95 duration-200 border border-emerald-400 shadow-lg shadow-emerald-200/50 flex flex-col items-center justify-center">
            <span class="text-lg">{{ getString('known_btn') }}</span>
            <span class="block text-[10px] font-medium opacity-90 mt-0.5 uppercase tracking-wider">🟢 {{ getString('action_next') }}</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const FALLBACKS = {
    showhint:    'Hinweis anzeigen',
    hint:        'Hinweis',
    taptoflip:   'Antippen zum Umdrehen',
    hard_btn:    'Schwer',
    action_back: 'Zurück',
    again_btn:   'Nochmal',
    action_stay: 'Bleibt',
    known_btn:   'Gewusst',
    action_next: 'Nächste',
};

const getString = (key) => {
    const moodleStr = window.M?.str?.mod_adaptivereview?.[key];
    if (moodleStr && !moodleStr.startsWith('[[')) return moodleStr;
    return FALLBACKS[key] ?? key;
};

const props = defineProps({
    card: { type: Object, required: true }
});

defineEmits(['rate']);

const isFlipped = ref(false);
const showHint = ref(false);

const toggleFlip = () => {
    isFlipped.value = !isFlipped.value;
};

watch(() => props.card, () => {
    isFlipped.value = false;
    showHint.value = false;
});
</script>

<style scoped>
.flip-container {
  perspective: 1200px;
}
.flip-card-inner {
  transform-style: preserve-3d;
}
.flipped .flip-card-inner {
  transform: rotateY(180deg);
}
.flip-card-front, .flip-card-back {
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
}
.flip-card-back {
  transform: rotateY(180deg);
}
</style>
