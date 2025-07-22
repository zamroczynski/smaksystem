<template>
  <div class="welcome-container">
    <div class="logo">
      <span class="smak">smak</span><span class="system">System</span>
    </div>
    <nav class="navigation">
      <a :href="route('login')" :class="['nav-link', { 'animate-in-left': animateButtons }]" class="login-button">Zaloguj</a>
      <a :href="route('about')" :class="['nav-link', { 'animate-in-right': animateButtons }]" class="about-button">Poznaj <span class="smak">smak</span>System</a>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'WelcomeView',
  data() {
    return {
      animateButtons: false, // Zmieniona nazwa zmiennej do animacji przycisków
    };
  },
  mounted() {
    // Opóźnienie przed uruchomieniem animacji wjazdu przycisków
    this.$nextTick(() => {
      setTimeout(() => {
        this.animateButtons = true;
      }, 300); // Przyciski zaczną wjeżdżać po 0.3 sekundy
    });
  },
  methods: {
    route(name) {
      const routes = {
        'login': '/login',
        'about': '/about'
      };
      return routes[name] || '#';
    }
  }
}
</script>

<style scoped>
.welcome-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #f0f2f5;
  font-family: Arial, sans-serif;
  overflow: hidden; /* Upewnia się, że elementy wjeżdżające nie powodują pasków przewijania */
}

.logo {
  font-size: 3em;
  font-weight: bold;
  margin-bottom: 2em;
  text-align: center;
}

.smak {
  color: #e74c3c;
  font-size: 1em;
}

.system {
  color: #ffffff;
  background-color: #34495e;
  padding: 0.2em 0.2em;
  border-radius: 5px;
  font-size: 1.8em;
  display: inline-block;
  line-height: 1;
}

.navigation {
  display: flex;
  gap: 1.5em;
  /* Usunięto początkowe opacity i transform z .navigation */
}

.nav-link {
  text-decoration: none;
  color: #3498db;
  font-size: 1.2em;
  padding: 0.5em 1em;
  border: 2px solid #3498db;
  border-radius: 5px;
  transition: all 0.3s ease; /* Zachowanie przejścia dla hover */

  /* Początkowy stan dla wszystkich przycisków */
  opacity: 0;
  transition: opacity 0.8s ease-out, transform 0.8s ease-out; /* Animacja wjazdu */
}

/* Specyficzne pozycje początkowe dla każdego przycisku */
.login-button {
  transform: translateX(-100vw); /* Zaczyna poza ekranem po lewej */
}

.about-button {
  transform: translateX(100vw); /* Zaczyna poza ekranem po prawej */
}

/* Stan końcowy, gdy animacja się uruchomi */
.login-button.animate-in-left {
  opacity: 1;
  transform: translateX(0); /* Wjeżdża na swoją pozycję */
}

.about-button.animate-in-right {
  opacity: 1;
  transform: translateX(0); /* Wjeżdża na swoją pozycję */
}

/* Możesz dodać opóźnienie, aby przyciski wjeżdżały po kolei */
.about-button.animate-in-right {
    transition-delay: 0.2s; /* Ten przycisk wjedzie 0.2s po pierwszym */
}


.nav-link:hover {
  background-color: #3498db;
  color: #ffffff;
}

/* Responsywność dla mniejszych ekranów */
@media (max-width: 767px) {
  .logo {
    font-size: 2em;
    margin-bottom: 1.5em;
  }

  .smak {
    font-size: 1em;
  }

  .system {
    font-size: 1.5em;
    padding: 0 0.15em;
  }

  .navigation {
    flex-direction: column;
    gap: 1em;
  }

  .nav-link {
    font-size: 1em;
    padding: 0.4em 0.8em;
    /* Resetujemy transformacje, aby na małych ekranach przyciski były widoczne od razu,
       chyba że chcesz inną animację mobilną */
    transform: translateX(0) !important;
    opacity: 1 !important;
    transition: none !important;
  }
}
</style>