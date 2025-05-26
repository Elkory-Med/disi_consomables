# DISI Commerce - Plateforme de Commerce Électronique

## Aperçu du Projet

DISI Commerce est une plateforme de commerce électronique moderne et riche en fonctionnalités, construite avec la Stack TALL (Tailwind CSS, Alpine.js, Laravel, Livewire). Conçue pour la simplicité, les performances et la flexibilité.

## 🚀 Fonctionnalités Principales

### Gestion des Utilisateurs
- Inscription et Authentification des Utilisateurs
- Gestion des Rôles Utilisateurs (Admin, Utilisateurs Approuvés)
- Gestion des Profils Utilisateurs

### Gestion des Produits
- Liste des Produits
- Page de Détails des Produits
- Opérations CRUD Produits pour Administrateurs
- Gestion des Catégories

### Gestion des Commandes
- Fonctionnalité de Panier
- Création de Commandes
- Suivi des Commandes
- Gestion des Commandes pour Administrateurs

### Fonctionnalités Supplémentaires
- Design Responsive
- Stylisation Tailwind CSS
- Composants Livewire
- Notifications Utilisateur Dynamiques

## 🛠 Stack Technologique

- **Backend**: Laravel 11
- **Frontend**: Livewire 3, Alpine.js
- **Stylisation**: Tailwind CSS
- **Authentification**: Implémentation Personnalisée

## 📂 Structure du Projet

```
app/
├── Livewire/
│   ├── AboutUs.php
│   ├── AddCategory.php
│   ├── AddProductForm.php
│   ├── AdminDashboard.php
│   ├── Auth/
│   ├── ManageOrders.php
│   ├── ManageUsers.php
│   ├── ProductDetails.php
│   └── ShoppingCartComponent.php
├── Models/
│   ├── Category.php
│   ├── Order.php
│   ├── Product.php
│   └── User.php
└── Http/
    ├── Controllers/
    └── Middleware/
```

## 🔧 Installation et Configuration

1. Clonez le dépôt
2. Installez les dépendances : `composer install`
3. Configurez le fichier `.env`
4. Exécutez les migrations : `php artisan migrate`
5. Démarrez le serveur de développement : `php artisan serve`

## 👤 Créateur

Ce projet a été développé par **Ely Cheikh Ahmed BELLAL**, **SOMELEC-DISI**un développeur passionné de technologies web modernes.

## 🌟 Contribution

Les contributions sont les bienvenues ! Suivez ces étapes :
1. Forkez le dépôt
2. Créez votre branche de fonctionnalité
3. Commitez vos modifications
4. Poussez vers la branche
5. Créez une Pull Request

## 📄 Licence

Ce projet est open-source. Référez-vous au fichier LICENSE pour plus de détails.
