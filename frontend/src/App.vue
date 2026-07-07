<template>
  <div class="min-h-[600px] w-full bg-slate-50 font-sans text-slate-900 py-10 px-4 rounded-3xl" style="font-family: 'Inter', system-ui, sans-serif;">
    <Dashboard v-if="!sessionActive" @start-session="handleStartSession" />
    
    <div v-else class="max-w-2xl mx-auto">
      <div class="flex flex-col sm:flex-row justify-between items-center mb-10 gap-4">
        <button @click="endSession" class="text-slate-500 hover:text-[#4F46E5] font-medium flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          {{ getString('backtodashboard') }}
        </button>
        <div class="text-sm font-bold px-4 py-1.5 rounded-full shadow-sm border flex items-center gap-2" :class="getBadgeClass(activeBox)">
          <span class="w-2 h-2 rounded-full" :class="getDotClass(activeBox)"></span>
          {{ getBoxName(activeBox) }} 
          <span class="opacity-40 mx-1 font-normal">|</span> 
          {{ getString('cardxofy_x') }} {{ Math.min(currentCardIndex + 1, sessionCards.length) }} {{ getString('cardxofy_y') }} <span class="font-extrabold">{{ sessionCards.length }}</span>
        </div>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-32 text-slate-500">
        <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="font-medium text-lg">{{ getString('loadingcards') }}</p>
      </div>
      
      <div v-else-if="sessionFinished" class="text-center py-12 bg-white rounded-3xl shadow-md border border-slate-100 p-8 transform transition-all max-w-lg mx-auto">
        <div class="text-6xl mb-4">{{ getSessionFeedback().emoji }}</div>
        <h3 class="text-2xl font-extrabold text-slate-800 mb-2 tracking-tight">{{ getSessionFeedback().title }}</h3>
        <p class="text-slate-500 mb-8">{{ getSessionFeedback().desc }}</p>
        
        <!-- Stats visual block -->
        <div class="mb-10 w-full mt-4">
            <div class="flex justify-between text-sm font-bold text-slate-600 mb-2 px-1">
               <span>{{ sessionCards.length }} {{ getString('cards') }}</span>
            </div>
            <div class="w-full flex h-4 rounded-full overflow-hidden shadow-inner bg-slate-100 mb-3">
               <div v-if="sessionStats.known > 0" :style="{ width: (sessionStats.known / sessionCards.length * 100) + '%' }" class="bg-emerald-400" :title="getString('known_btn')"></div>
               <div v-if="sessionStats.again > 0" :style="{ width: (sessionStats.again / sessionCards.length * 100) + '%' }" class="bg-amber-400" :title="getString('again_btn')"></div>
               <div v-if="sessionStats.hard > 0" :style="{ width: (sessionStats.hard / sessionCards.length * 100) + '%' }" class="bg-red-400" :title="getString('hard_btn')"></div>
            </div>
            <div class="flex justify-center flex-wrap gap-4 mt-1 text-xs font-semibold text-slate-600">
               <div v-if="sessionStats.known > 0" class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> {{ sessionStats.known }} {{ getString('known_btn') }}</div>
               <div v-if="sessionStats.again > 0" class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> {{ sessionStats.again }} {{ getString('again_btn') }}</div>
               <div v-if="sessionStats.hard > 0" class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> {{ sessionStats.hard }} {{ getString('hard_btn') }}</div>
            </div>
        </div>

        <button @click="endSession" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3.5 px-8 rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 w-full">
          {{ getString('backtodashboard') }}
        </button>
      </div>
      
      <div v-else class="flex flex-col items-center w-full perspective-1000">
        <Transition name="fade-slide" mode="out-in">
          <Card :key="currentCard.id" :card="currentCard" @rate="handleRate" />
        </Transition>
      </div>
      
      <!-- Progress Bar (Hide during finished screen) -->
      <div v-if="!loading && !sessionFinished && sessionCards.length > 0" class="mt-14 max-w-md mx-auto relative group">
        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden shadow-inner">
          <div class="bg-gradient-to-r from-indigo-400 to-violet-500 h-2 rounded-full transition-all duration-500 ease-out" :style="{ width: `${progressPercentage}%` }"></div>
        </div>
        <div class="absolute -top-6 text-center w-full text-xs font-bold text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">
            {{ Math.round(progressPercentage) }}% {{ getString('completed') }}
        </div>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Dashboard from './components/Dashboard.vue';
import Card from './components/Card.vue';
import { getCardsByBox, submitAnswer } from './api';

const FALLBACKS = {
    sessiondone: 'Alles erledigt!',
    sessiondonedesc: 'Gute Arbeit. Gehe zurück zum Dashboard für den nächsten Stapel.',
    feedback_grand_title: 'Meisterwerk',
    feedback_grand_desc: 'Herzlichen Glückwunsch! Du hast alle Karten im System bis in den Experten-Stapel gebracht und zudem völlig fehlerfrei wiedergegeben. Du hast dieses Thema absolut verstanden!',
    feedback_perfect_title: 'Starkes Ergebnis',
    feedback_perfect_desc: 'Du hast alle Inhalte korrekt ins Gedächtnis gerufen. Das Material sitzt.',
    feedback_good_title: 'Solide Leistung',
    feedback_good_desc: 'Du hast einen guten Überblick. Die verbleibenden Lücken schließen sich mit der Zeit.',
    feedback_okay_title: 'Auf dem richtigen Weg',
    feedback_okay_desc: 'Die Basis steht. Eine weitere Wiederholung wird dein Wissen festigen.',
    feedback_learn_title: 'Wiederholung empfohlen',
    feedback_learn_desc: 'Einige Antworten fielen dir schwer. Nutze die nächste Runde, um diese trainieren.',
    completed: 'Abgeschlossen',
    loadingcards: 'Karten werden geladen...',
    cards: 'Karten',
    known_btn: 'Gewusst',
    again_btn: 'Nochmal',
    hard_btn: 'Schwer',
    backtodashboard: 'Zurück zum Dashboard',
    cardxofy_x: 'Karte',
    cardxofy_y: 'von',
    box0: 'Neu',
    box1: 'Einsteiger',
    box2: 'Lernender',
    box3: 'Fortgeschritten',
    box4: 'Erfahren',
    box5: 'Experte',
};

const getString = (key) => {
    // 1. Try Moodle's loaded strings
    const moodleStr = window.M?.str?.mod_adaptivereview?.[key];
    // 2. Only use Moodle string if it's valid (not a [[placeholder]])
    if (moodleStr && !moodleStr.startsWith('[[')) return moodleStr;
    // 3. Fall back to hardcoded defaults
    return FALLBACKS[key] ?? key;
};

const sessionActive = ref(false);
const sessionFinished = ref(false);
const activeBox = ref(null);
const loading = ref(false);
const sessionCards = ref([]);
const currentCardIndex = ref(0);
const sessionStats = ref({ known: 0, again: 0, hard: 0 });
const totalCardsInSystem = ref(0);

const currentCard = computed(() => sessionCards.value[currentCardIndex.value]);

const progressPercentage = computed(() => {
    if (sessionCards.value.length === 0) return 100;
    return (currentCardIndex.value / sessionCards.value.length) * 100;
});

const getSessionFeedback = () => {
    const total = sessionCards.value.length;
    if (total === 0) return { emoji: '🎉', title: getString('sessiondone'), desc: getString('sessiondonedesc') };
    
    // Grand Finale check: all cards in system are in Box 5 and answered perfectly
    if (activeBox.value === 5 && total === totalCardsInSystem.value && sessionStats.value.known === total) {
        return { emoji: '🎓', title: getString('feedback_grand_title'), desc: getString('feedback_grand_desc') };
    }

    // Weight calculation to determine performance
    const score = (sessionStats.value.known + (sessionStats.value.again * 0.5)) / total;
    
    if (score >= 0.9) {
        return { emoji: '🏆', title: getString('feedback_perfect_title'), desc: getString('feedback_perfect_desc') };
    } else if (score >= 0.7) {
        return { emoji: '🌟', title: getString('feedback_good_title'), desc: getString('feedback_good_desc') };
    } else if (score >= 0.4) {
        return { emoji: '👍', title: getString('feedback_okay_title'), desc: getString('feedback_okay_desc') };
    } else {
        return { emoji: '💪', title: getString('feedback_learn_title'), desc: getString('feedback_learn_desc') };
    }
};

const getBadgeClass = (box) => {
    const classes = [
        'bg-blue-50 text-blue-700 border-blue-200',
        'bg-orange-50 text-orange-800 border-orange-200',
        'bg-yellow-50 text-yellow-800 border-yellow-200',
        'bg-lime-50 text-lime-800 border-lime-200',
        'bg-emerald-50 text-emerald-800 border-emerald-200',
        'bg-green-100 text-green-800 border-green-300'
    ];
    return classes[box] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const getDotClass = (box) => {
    const classes = ['bg-blue-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-emerald-500', 'bg-green-600'];
    return classes[box] || 'bg-slate-500';
};

const getBoxName = (box) => {
    return getString('box' + box);
};

const handleStartSession = async (boxnumber, totalSystemCards) => {
    activeBox.value = boxnumber;
    totalCardsInSystem.value = totalSystemCards;
    sessionActive.value = true;
    sessionFinished.value = false;
    loading.value = true;
    currentCardIndex.value = 0;
    sessionStats.value = { known: 0, again: 0, hard: 0 };
    try {
        sessionCards.value = await getCardsByBox(boxnumber);
    } catch (e) {
        console.error("Error loading session:", e);
        alert(getString('error_loading_cards'));
        sessionActive.value = false;
    } finally {
        loading.value = false;
    }
};

const handleRate = async (rating) => {
    if (!currentCard.value) return;
    
    if (rating === 0) sessionStats.value.hard++;
    else if (rating === 1) sessionStats.value.again++;
    else if (rating === 2) sessionStats.value.known++;

    // Fire and forget answer submission to Moodle API
    submitAnswer(currentCard.value.id, rating).catch(e => {
        console.error("Failed to save answer for card", currentCard.value.id, e);
    });

    // Move to next card locally
    if (currentCardIndex.value < sessionCards.value.length - 1) {
        currentCardIndex.value++;
    } else {
        // Session complete
        currentCardIndex.value++; // triggers 100% progress momentarily
        setTimeout(() => {
            sessionFinished.value = true;
        }, 500);
    }
};

const endSession = () => {
    sessionActive.value = false;
    sessionFinished.value = false;
    sessionCards.value = [];
    activeBox.value = null;
    sessionStats.value = { known: 0, again: 0, hard: 0 };
};
</script>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(30px) rotateY(10deg);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px) rotateY(-10deg);
}
.perspective-1000 {
  perspective: 1000px;
}
</style>
