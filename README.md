
# 📦 Laravel API - Gestion des Produits

## 📖 Description

Ce projet est une API REST développée avec **Laravel** permettant de gérer des produits.

L’API offre les fonctionnalités suivantes :

* CRUD complet des produits
* Association des produits à des catégories
* Pagination des résultats
* Activation / désactivation des produits
* Filtrage des produits par catégorie

---

## ⚙️ Fonctionnalités

* 📄 Lister les produits avec pagination
* ➕ Créer un produit avec validation
* 🔍 Voir un produit spécifique
* ✏️ Modifier un produit
* ❌ Supprimer un produit
* 🗂️ Filtrer les produits par catégorie
* 🔄 Activer / désactiver un produit

---

## 🛠️ Technologies utilisées

* PHP
* Laravel
* Eloquent ORM
* API REST
* MySQL

---

## 🚀 Installation

```bash
git clone https://github.com/JordanLJ/laravel-api-manage-product.git
cd laravel-api-manage-product
composer install
cp .env.example .env
php artisan key:generate
```

Configurer la base de données dans `.env`, puis :

```bash
php artisan migrate
php artisan serve
```

---

## 📌 Endpoints API

### 🔍 Récupérer tous les produits

```http
GET /api/products
```

* Pagination : 10 produits par page
* Inclut les catégories (`with('category')`)

---

### ➕ Créer un produit

```http
POST /api/products
```

#### Body JSON :

```json
{
  "name": "Produit A",
  "description": "Description du produit",
  "price": 100,
  "quantity": 10,
  "sku": "PROD001",
  "category_id": 1,
  "is_active": true
}
```

---

### 📄 Afficher un produit

```http
GET /api/products/{id}
```

---

### ✏️ Modifier un produit

```http
PUT /api/products/{id}
```

---

### ❌ Supprimer un produit

```http
DELETE /api/products/{id}
```

---

### 🗂️ Filtrer par catégorie

```http
GET /api/products/category/{category}
```

---

### 🔄 Activer / Désactiver un produit

```http
PATCH /api/products/{id}/toggle-active
```

---

## 🧠 Logique technique

* Utilisation de `ProductResource` pour formater les réponses
* Validation des données via `Request::validate()`
* Relation avec les catégories (`category_id`)
* Pagination avec `paginate(10)`
* Toggle du statut actif avec inversion booléenne

---

## 🔐 Validation des données

### Création :

* `name` : requis
* `price` : requis, numérique ≥ 0
* `quantity` : entier ≥ 0
* `sku` : unique
* `category_id` : doit exister
* `is_active` : booléen

---

## 📁 Structure du projet

```
app/
 ├── Http/
 │   ├── Controllers/
 │   │   └── ProductController.php
 │   ├── Resources/
 │   │   └── ProductResource.php
 ├── Models/
 │   └── Product.php
```

---

## ⚠️ Remarques importantes

* ⚠️ Incohérence dans ton controller :

  * `store()` utilise `category_id`
  * `update()` utilise `category` ❌
    👉 Il faut uniformiser en `category_id`

* La méthode `getByCategory()` utilise `category` (string) → à revoir si relation DB

---

## ✅ Améliorations possibles

* Authentification (Laravel Sanctum / JWT)
* Upload d’images produits
* Recherche avancée
* Gestion des stocks
* Documentation Swagger

---

## 👨‍💻 Auteur

**JordanLJ**

