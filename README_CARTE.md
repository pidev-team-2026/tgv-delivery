# 🗺️ CARTE INTERACTIVE - GUIDE COMPLET

## ✅ FONCTIONNALITÉS INCLUSES

### 🎯 **1. CARTE INTERACTIVE LEAFLET**
- ✅ Carte complète de la Tunisie (OpenStreetMap)
- ✅ Zoom/Dézoom fluide
- ✅ Vue centrée sur la Tunisie par défaut
- ✅ Mode plein écran disponible

---

### 📍 **2. SÉLECTION DE LOCALISATION**

#### **Méthode 1 : Clic sur la carte**
- Cliquez n'importe où sur la carte
- Un marqueur vert apparaît
- L'adresse est récupérée automatiquement (reverse geocoding)

#### **Méthode 2 : Recherche d'adresse**
- Tapez dans le champ de recherche
- Suggestions automatiques (villes tunisiennes)
- Utilise l'API Nominatim pour la recherche globale
- Sélectionnez une suggestion → la carte se centre automatiquement

#### **Méthode 3 : Géolocalisation automatique**
- Bouton "Me localiser"
- Détecte votre position GPS actuelle
- Place le marqueur automatiquement

---

### 🎨 **3. INTERFACE UTILISATEUR**

#### **Sidebar (Gauche)**
- 🔍 Champ de recherche avec autocomplete
- 📍 Bouton géolocalisation
- 🔄 Bouton réinitialisation
- 📊 Affichage de l'adresse sélectionnée
- 🌍 Affichage des coordonnées GPS
- 🚚 Bouton "Continuer vers la commande"

#### **Carte (Droite)**
- 🗺️ Carte interactive pleine largeur
- ➕➖ Contrôles de zoom
- 🖥️ Bouton plein écran
- 📌 Marqueur déplaçable (drag & drop)

---

### 🔥 **4. FONCTIONNALITÉS AVANCÉES**

#### **Marqueur draggable**
```javascript
// Le marqueur peut être déplacé à la souris
// L'adresse se met à jour automatiquement
```

#### **Reverse Geocoding**
```javascript
// Coordonnées → Adresse automatique
// Utilise l'API Nominatim (gratuite)
// Affichage en français
```

#### **Recherche intelligente**
```javascript
// Recherche locale (villes tunisiennes)
// Recherche API (toutes adresses)
// Délai de 300ms pour éviter trop de requêtes
// Limite de 5 suggestions maximum
```

#### **Popup d'information**
```javascript
// Click sur marqueur → popup avec adresse + coords
// Design moderne
// Auto-centrage
```

---

## 📦 **VILLES TUNISIENNES INCLUSES**

Le système contient 18 villes principales :
- Tunis, Sfax, Sousse, Kairouan
- Bizerte, Gabès, Ariana, Ben Arous
- Monastir, Nabeul, Médenine, Mahdia
- La Marsa, Carthage, Hammamet, Djerba
- Tozeur, Gafsa

---

## 🛠️ **APIS UTILISÉES**

### **1. Leaflet.js**
```html
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
```
**Pourquoi ?** Bibliothèque de cartographie open-source, gratuite, performante

### **2. OpenStreetMap (Tuiles)**
```javascript
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
```
**Pourquoi ?** Cartes gratuites, haute qualité, mises à jour régulières

### **3. Nominatim API (Geocoding)**
```javascript
// Recherche d'adresse
https://nominatim.openstreetmap.org/search

// Reverse geocoding
https://nominatim.openstreetmap.org/reverse
```
**Pourquoi ?** API gratuite, pas de clé nécessaire, données OSM

---

## 🚀 **WORKFLOW UTILISATEUR**

```
1. Page charge → Carte Tunisie s'affiche
   ↓
2. Utilisateur sélectionne localisation (3 méthodes)
   ↓
3. Marqueur placé + Adresse récupérée
   ↓
4. Bouton "Continuer" activé
   ↓
5. Click → Redirection vers /client/produits avec:
   - Latitude
   - Longitude
   - Adresse complète
```

---

## 🎨 **DESIGN**

### **Palette de couleurs**
- Primaire: `#1e3a5f` (Bleu foncé)
- Accent: `#2ecc71` (Vert)
- Blanc: `#ffffff`
- Gris clair: `#f8f9fa`

### **Animations**
- ✅ Hover effects sur boutons
- ✅ Transitions fluides
- ✅ Spinner de chargement
- ✅ Fade in/out des suggestions

### **Responsive**
- ✅ Desktop: Sidebar + Carte côte à côte
- ✅ Tablette: Empilé verticalement
- ✅ Mobile: Optimisé pour petits écrans

---

## 📝 **UTILISATION**

### **1. Remplacer le fichier**
```bash
templates/client/index.html.twig
```

### **2. Tester**
```bash
symfony serve
# Ouvrir http://localhost:8000/client
```

### **3. Interactions possibles**
- Cliquer sur la carte
- Taper dans la recherche
- Cliquer "Me localiser"
- Déplacer le marqueur
- Zoomer/Dézoomer
- Plein écran

---

## 🔧 **CUSTOMISATION**

### **Changer le centre par défaut**
```javascript
const TUNISIA_CENTER = [36.8065, 10.1815]; // Tunis au lieu du centre
```

### **Ajouter des villes**
```javascript
const TUNISIAN_CITIES = {
    'Nouvelle Ville': { coords: [lat, lng], gov: 'Gouvernorat' },
    // ...
};
```

### **Changer le zoom**
```javascript
const DEFAULT_ZOOM = 10; // Plus proche
const CITY_ZOOM = 15;    // Encore plus proche
```

### **Personnaliser le marqueur**
```css
.custom-marker {
    background: #ff6b35; /* Orange au lieu de vert */
}
```

---

## ⚡ **PERFORMANCES**

### **Optimisations incluses**
- ✅ Debounce sur la recherche (300ms)
- ✅ Limite de 5 suggestions
- ✅ Cache des villes locales
- ✅ Lazy loading de la carte
- ✅ Requêtes API minimisées

### **Temps de chargement**
- Carte: ~1s
- Recherche: ~200-500ms
- Reverse geocoding: ~300-800ms

---

## 🐛 **DÉPANNAGE**

### **La carte ne s'affiche pas**
```bash
# Vérifier la console navigateur (F12)
# Vérifier que Leaflet est chargé
# Vérifier la connexion internet
```

### **La géolocalisation ne fonctionne pas**
```bash
# Le navigateur doit être en HTTPS (ou localhost)
# L'utilisateur doit autoriser la géolocalisation
# Vérifier les permissions du navigateur
```

### **Les suggestions ne s'affichent pas**
```bash
# Vérifier la console pour erreurs API
# Nominatim peut avoir des limites de taux
# Attendre quelques secondes et réessayer
```

---

## 🚀 **PROCHAINES AMÉLIORATIONS (Optionnel)**

### **1. Zones de livraison**
```javascript
// Afficher les zones couvertes sur la carte
// Polygones colorés pour chaque zone
```

### **2. Calcul de distance**
```javascript
// Distance entre position et point de livraison
// Estimation du temps de livraison
```

### **3. Prix selon distance**
```javascript
// Calcul automatique des frais de livraison
// Affichage en temps réel
```

### **4. Historique des adresses**
```javascript
// Sauvegarder les adresses précédentes
// Suggestions basées sur l'historique
```

---

## ✅ **RÉSULTAT FINAL**

Tu as maintenant une **carte interactive professionnelle** avec :
- ✅ Recherche d'adresse intelligente
- ✅ Géolocalisation automatique
- ✅ Interface moderne et responsive
- ✅ Intégration Tunisie complète
- ✅ APIs gratuites et performantes
- ✅ Code propre et commenté

**Prêt à tester ! 🚀**
