import Alpine from "alpinejs";
import tileForm from "./components/tileForm";
import seating from "./components/seating";
import turnTimer from "./components/turnTimer";
import gameWatcher from "./components/gameWatcher";
import liveGame from "./components/liveGame";
import cookieConsent from "./components/cookieConsent";

Alpine.data("tileForm", tileForm);
Alpine.data("seating", seating);
Alpine.data("turnTimer", turnTimer);
Alpine.data("gameWatcher", gameWatcher);
Alpine.data("liveGame", liveGame);
Alpine.data("cookieConsent", cookieConsent);

window.Alpine = Alpine;
Alpine.start();
