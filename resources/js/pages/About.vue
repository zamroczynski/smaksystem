<template>
  <div class="min-h-screen font-sans text-gray-800 bg-white">
    <div class="header-section p-10 max-w-6xl mx-auto">
      <h1 class="text-right text-5xl font-bold m-0">
        <a :href="route('welcome')" class="no-underline text-gray-800">
          <span class="text-red-500">smak</span><span class="text-white bg-gray-700 px-1 rounded">System</span>
        </a>
      </h1>
    </div>

    <div :class="['full-screen-section', 'first-entry', { 'animate-in': animateFirst }]">
      <div class="content-wrapper">
        <img src="/images/image6.jpg" alt="Obraz 1" class="image-style rounded-lg shadow-lg" />
        <p class="text-content paragraph-bg-1">
          <span class="text-red-500">smak</span>System to nowoczesne i intuicyjne rozwiązanie stworzone z myślą o restauracjach, barach i punktach gastronomicznych działających na polskim rynku. Nasz system łączy w sobie wszystko, czego potrzebuje współczesna gastronomia: atrakcyjną stronę internetową z informacjami i menu, wygodną aplikację do zamawiania jedzenia dla klientów oraz zaawansowany panel administracyjny dla właścicieli i menedżerów. Dzięki <span class="text-red-500">smak</span>System możliwa jest kompleksowa obsługa zarówno klientów, jak i codziennej działalności lokalu — wszystko w jednym, spójnym środowisku.
        </p>
      </div>
    </div>

    <div :class="['full-screen-section', 'flex-row-reverse-desktop', { 'animate-in': animateSecond }]" ref="secondSection">
      <div class="content-wrapper">
        <img src="/images/image2.jpg" alt="Obraz 2" class="image-style rounded-lg shadow-lg animated-image right-entry" />
        <p class="text-content paragraph-bg-2 animated-text-entry left-entry">
          Dla właścicieli i menedżerów przygotowaliśmy rozbudowany panel administracyjny, który pozwala na zarządzanie personelem, kontrolę stanów magazynowych, ewidencję produktów i dat przydatności oraz monitorowanie działania całej restauracji w czasie rzeczywistym. Dzięki temu restauracje korzystające ze <span class="text-red-500">smak</span>System mają nie tylko większą kontrolę nad codziennymi procesami, ale również mogą skuteczniej planować zakupy, zapobiegać marnotrawstwu i zwiększać efektywność zespołu.
        </p>
      </div>
    </div>

    <div :class="['full-screen-section', { 'animate-in': animateThird }]" ref="thirdSection">
      <div class="content-wrapper">
        <img src="/images/image3.jpg" alt="Obraz 3" class="image-style rounded-lg shadow-lg animated-image left-entry" />
        <p class="text-content paragraph-bg-3 animated-text-entry right-entry">
          <span class="text-red-500">smak</span>System zdobył zaufanie wielu właścicieli lokali gastronomicznych w całej Polsce. Cenią nas za przejrzystość, stabilność działania i realne wsparcie w prowadzeniu biznesu. Co ważne, pozytywne opinie płyną nie tylko od zarządzających — także pracownicy restauracji chwalą sobie prostotę i intuicyjność systemu, który ułatwia im codzienną pracę, od przyjmowania zamówień po kontrolę zapasów i grafiki.
        </p>
      </div>
    </div>

    <div :class="['full-screen-section', 'flex-row-reverse-desktop', { 'animate-in': animateFourth }]" ref="fourthSection">
      <div class="content-wrapper">
        <img src="/images/image5.jpg" alt="Obraz 4" class="image-style rounded-lg shadow-lg animated-image right-entry" />
        <p class="text-content paragraph-bg-4 animated-text-entry left-entry">
          Jesteśmy niewielkim zespołem pasjonatów, który od początku postawił na jakość i indywidualne podejście do klienta. Każda restauracja, która dołącza do <span class="text-red-500">smak</span>System, wnosi coś unikalnego — dlatego potrzeby jednych lokali pomagają nam rozwijać funkcje, z których korzystają wszyscy. Tworzymy społeczność, w której dzielimy się sprawdzonymi rozwiązaniami, i stale słuchamy opinii naszych użytkowników, by <span class="text-red-500">smak</span>System był nie tylko narzędziem, ale realnym wsparciem w gastronomicznej codzienności.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AboutView',
  data() {
    return {
      animateFirst: false,
      animateSecond: false,
      animateThird: false,
      animateFourth: false,
      observer: null,
    };
  },
  mounted() {
    this.$nextTick(() => {
      setTimeout(() => {
        this.animateFirst = true;
      }, 100);
    });

    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            if (entry.target === this.$refs.secondSection) {
              this.animateSecond = true;
            } else if (entry.target === this.$refs.thirdSection) {
              this.animateThird = true;
            } else if (entry.target === this.$refs.fourthSection) {
              this.animateFourth = true;
            }
            this.observer.unobserve(entry.target);
          }
        });
      },
      {
        root: null,
        rootMargin: '0px',
        threshold: 0.2,
      }
    );

    if (this.$refs.secondSection) {
      this.observer.observe(this.$refs.secondSection);
    }
    if (this.$refs.thirdSection) {
      this.observer.observe(this.$refs.thirdSection);
    }
    if (this.$refs.fourthSection) {
      this.observer.observe(this.$refs.fourthSection);
    }
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  methods: {
    route(name) {
      const routes = {
        'welcome': '/',
        'login': '/login',
        'about': '/about',
      };
      return routes[name] || '#';
    }
  }
}
</script>

<style scoped>
/* Kontener dla całej strony */
.min-h-screen {
    /* Tailwind już dodaje `min-h-screen` */
}

/* Klasa dla sekcji nagłówka */
.header-section {
    height: 10vh; /* Niska wysokość dla nagłówka */
    display: flex;
    justify-content: flex-end; /* Wyrównanie zawartości do prawej */
    align-items: center; /* Wyśrodkowanie pionowe */
    padding: 0 40px; /* Padding poziomy */
    box-sizing: border-box;
}

/* Styl dla samego nagłówka H1 */
h1 {
    margin: 0; /* Usunięcie domyślnych marginesów */
    text-align: right; /* Wyrównanie tekstu wewnątrz h1 do prawej */
    flex-shrink: 0; /* Zapobiega zmniejszaniu się h1 */
}

.full-screen-section {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 40px;
  box-sizing: border-box;
  overflow: hidden;
}

.content-wrapper {
  display: flex;
  align-items: center;
  max-width: 1200px;
  width: 100%;
  gap: 40px;
}

/* Używamy flex-row-reverse-desktop aby odwrócić kolejność tylko na większych ekranach */
.flex-row-reverse-desktop .content-wrapper {
  flex-direction: row-reverse;
}

/* ZMIENIONE STYLE DLA ZDJĘĆ - POWRÓT DO SKALOWANIA BEZ PRZYCINANIA */
.image-style {
  width: 50%; /* Domyślnie zajmuje 50% szerokości content-wrapper */
  max-width: 500px; /* Maksymalna szerokość obrazu */
  height: auto; /* WYCOFANO STAŁĄ WYSOKOŚĆ - obraz skaluje się proporcjonalnie */
  object-fit: contain; /* KLUCZOWE: Obraz będzie przeskalowany, ale nie ucięty */
  flex-shrink: 0;
}

/* ZMIENIONY ROZMIAR TEKSTU W AKAPITACH */
.text-content {
  flex: 1;
  padding: 20px;
  line-height: 1.8;
  font-size: 1.25em; /* Zwiększony rozmiar czcionki (np. 1.25em, czyli 20px przy domyślnej 16px) */
  text-align: justify;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  max-height: 80vh;
  overflow-y: auto;
}

/* Domyślny stan początkowy dla pierwszej sekcji (zdjęcie i akapit) */
.first-entry .content-wrapper .image-style,
.first-entry .content-wrapper p {
  opacity: 0;
  transform: translateX(-100%);
  transition: opacity 1s ease-out, transform 1s ease-out;
}

/* Stan końcowy, gdy klasa 'animate-in' zostanie dodana do first-entry */
.first-entry.animate-in .content-wrapper .image-style,
.first-entry.animate-in .content-wrapper p {
  opacity: 1;
  transform: translateX(0);
}


/* Style dla pozostałych sekcji - domyślny stan początkowy */
.full-screen-section:not(.first-entry) .content-wrapper .image-style,
.full-screen-section:not(.first-entry) .content-wrapper .animated-text-entry {
  opacity: 0;
  transition: opacity 1s ease-out, transform 1s ease-out;
}

/* Specyficzne pozycje początkowe dla zdjęć w pozostałych sekcjach */
.animated-image.left-entry {
  transform: translateX(-100%);
}

.animated-image.right-entry {
  transform: translateX(100%);
}

/* Pozycje początkowe dla akapitów w pozostałych sekcjach */
.animated-text-entry.left-entry {
  transform: translateX(-100%);
}
.animated-text-entry.right-entry {
  transform: translateX(100%);
}

/* Stan końcowy, gdy klasa 'animate-in' zostanie dodana do sekcji */
.full-screen-section.animate-in .content-wrapper .image-style,
.full-screen-section.animate-in .content-wrapper .animated-text-entry {
  opacity: 1;
  transform: translateX(0);
}

/* Delay dla animacji w ramach tej samej sekcji (obraz i akapit) */
.full-screen-section.animate-in .content-wrapper .image-style { transition-delay: 0s; }
.full-screen-section.animate-in .content-wrapper .animated-text-entry { transition-delay: 0.2s; }


/* Responsywność dla mniejszych ekranów - breakpoint md (768px) */
@media (max-width: 767px) {
  /* Nagłówek na małych ekranach */
  .header-section {
    height: auto;
    padding: 15px 20px;
    justify-content: center; /* Centrowanie na małych ekranach */
  }

  h1 {
    text-align: center; /* Centrowanie tekstu na małych ekranach */
  }

  .full-screen-section {
    min-height: auto;
    padding: 20px;
  }

  .content-wrapper {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }

  .flex-row-reverse-desktop .content-wrapper {
    flex-direction: column;
  }

  /* ZMNIEJSZENIE ROZMIARU ZDJĘĆ NA MNIEJSZYCH EKRANACH */
  .image-style {
    width: 90%; /* Zwiększona szerokość, aby zajmować więcej miejsca */
    height: auto; /* Skalowanie auto */
    max-width: none;
  }

  /* ZMNIEJSZENIE ROZMIARU TEKSTU NA MNIEJSZYCH EKRANACH */
  .text-content {
    font-size: 1.1em; /* Nieco mniejszy tekst na małych ekranach */
    max-height: none;
    overflow-y: visible;
    width: 100%;
  }

  /* Reset animacji dla responsywności */
  .full-screen-section .image-style,
  .full-screen-section p {
    transform: translateX(0) !important;
    opacity: 1 !important;
    transition: none !important;
  }
}
</style>