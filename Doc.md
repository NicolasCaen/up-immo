# Documentation du Plugin UP-IMMO

## Présentation générale

Le plugin "UP-IMMO" est une solution de gestion immobilière pour WordPress spécialisée dans les biens immobiliers anciens. Il permet l'importation de biens immobiliers à partir de fichiers CSV, la gestion de ces biens via un type de contenu personnalisé, et offre des fonctionnalités d'administration avancées.

## Fonctionnalités principales

1. **Gestion des biens immobiliers**
   - Type de contenu personnalisé "Bien"
   - Métadonnées spécifiques (prix, surface, pièces, etc.)
   - Taxonomies personnalisées (Type de bien, Ville, État, Disponibilité)

2. **Importation de données**
   - Import de biens depuis des fichiers CSV
   - Support des fichiers ZIP contenant des CSV
   - Mapping configurable des colonnes CSV vers les champs du bien
   - Gestion automatique des encodages

3. **Administration**
   - Interface d'administration dédiée
   - Page de paramètres pour configurer le plugin
   - Page d'importation pour gérer les imports
   - Gestion des tâches planifiées (CRON)

4. **Intégration Gutenberg**
   - Blocs personnalisés pour afficher les biens
   - Support complet de l'éditeur WordPress moderne

## Architecture du plugin

Le plugin est organisé selon une architecture orientée objet avec les composants suivants :

- **Core** : Composants principaux du plugin (Plugin, Singleton, GutenbergManager, CronManager)
- **Admin** : Interfaces d'administration (AdminPage, SettingsPage, ImportPage, AdminAjax)
- **PostTypes** : Définition du type de contenu "Bien"
- **Taxonomies** : Définition des taxonomies personnalisées
- **Import** : Gestion de l'importation (ImportManager, ImportContext, Strategies)
- **Filters** : Filtres de contenu pour le traitement des données
- **Helpers** : Fonctions utilitaires
- **Models** : Modèles de données

## Installation et configuration

1. Téléchargez et activez le plugin via le gestionnaire de plugins WordPress
2. Accédez à la page de configuration "UP-IMMO" dans le menu d'administration
3. Configurez le mapping des colonnes CSV pour l'importation
4. Importez vos premiers biens immobiliers

## Utilisation

### Importation de biens

1. Accédez à la page "Importation" dans le menu "UP-IMMO"
2. Sélectionnez un fichier CSV ou ZIP contenant des données de biens
3. Lancez l'importation
4. Consultez les logs pour vérifier le bon déroulement

### Gestion des biens

1. Accédez à la section "Biens" dans le menu d'administration
2. Ajoutez, modifiez ou supprimez des biens
3. Gérez les métadonnées et taxonomies associées

### Affichage des biens sur le site

Utilisez les blocs Gutenberg fournis ou créez des modèles personnalisés pour afficher les biens sur votre site.

## Extension des métadonnées

### Ajout de nouvelles métadonnées pour les biens

Le plugin UP-IMMO permet d'étendre facilement les métadonnées associées aux biens immobiliers. Voici comment procéder :

#### 1. Modification du mapping d'importation

Pour ajouter une nouvelle métadonnée qui sera importée depuis vos fichiers CSV :

1. Accédez à la page de configuration "UP-IMMO" dans le menu d'administration
2. Dans la section "Mapping CSV", ajoutez votre nouvelle métadonnée en spécifiant :
   - Le nom du champ (par exemple : `surface_terrain`)
   - L'index de la colonne correspondante dans votre fichier CSV
3. Sauvegardez les modifications

#### 2. Enregistrement programmatique des métadonnées

Pour les développeurs souhaitant ajouter des métadonnées via code, utilisez la fonction `register_post_meta` de WordPress :

```php
register_post_meta('bien', 'nom_du_champ', [
    'type' => 'string', // Peut être 'string', 'number', 'integer', 'boolean', etc.
    'single' => true,    // true pour une valeur unique, false pour un tableau de valeurs
    'show_in_rest' => true, // Rendre accessible via l'API REST
    'sanitize_callback' => function($meta_value) {
        // Fonction de nettoyage personnalisée
        return sanitize_text_field($meta_value);
    }
]);
```

Ce code peut être ajouté dans une fonction accrochée à l'action `init` de WordPress.

#### 3. Ajout des champs dans l'interface d'administration

Pour ajouter le champ dans l'interface d'édition des biens :

1. Créez une classe qui étend `BienPostType` ou modifiez la méthode `getMetaFields()` dans la classe existante
2. Ajoutez votre nouveau champ à la configuration :

```php
protected function getMetaFields(): array {
    $fields = parent::getMetaFields();
    
    // Ajout d'un nouveau champ
    $fields['surface_terrain'] = [
        'label' => __('Surface du terrain:', 'up-immo'),
        'type' => 'number',
        'step' => '0.01',
    ];
    
    return $fields;
}
```

#### 4. Utilisation dans les templates

Pour utiliser vos nouvelles métadonnées dans les templates :

```php
$surface_terrain = get_post_meta(get_the_ID(), 'surface_terrain', true);
echo "Surface du terrain : {$surface_terrain} m²";
```

Ou via les blocs Gutenberg personnalisés fournis par le plugin.

---

# Dictionnaire des variables

## Constantes

| Constante | Description | Valeur par défaut |
|-----------|-------------|-------------------|
| `UP_IMMO_VERSION` | Version du plugin | '1.0.0' |
| `UP_IMMO_PLUGIN_FILE` | Chemin vers le fichier principal du plugin | `__FILE__` |
| `UP_IMMO_PATH` | Chemin absolu vers le répertoire du plugin | `plugin_dir_path(__FILE__)` |
| `UP_IMMO_URL` | URL vers le répertoire du plugin | `plugin_dir_url(__FILE__)` |
| `DEBUG_UP_IMMO` | Mode debug du plugin | `true` ou `WP_DEBUG` |
| `UP_IMMO_PLUGIN_DIR` | Chemin absolu vers le répertoire du plugin (alias) | `plugin_dir_path(__FILE__)` |

## Options WordPress

| Option | Description | Format |
|--------|-------------|--------|
| `up_immo_mapping_json` | Configuration du mapping CSV | JSON |
| `up_immo_import_path` | Dernier chemin d'importation utilisé | String |
| `up_immo_import_logs` | Logs d'importation | Array |

## Métadonnées des biens

| Métadonnée | Description | Type |
|------------|-------------|------|
| `reference` | Référence unique du bien | String |
| `titre` | Titre du bien | String |
| `description` | Description détaillée | String |
| `prix` | Prix du bien | Number |
| `surface` | Surface en m² | Number |
| `pieces` | Nombre de pièces | Integer |
| `chambres` | Nombre de chambres | Integer |
| `code_postal` | Code postal | String |
| `ville` | Ville | String |
| `dpe` | Diagnostic de performance énergétique | String (A-G) |
| `contact_tel` | Téléphone de contact | String |
| `contact_email` | Email de contact | String |
| `attached_images` | IDs des images attachées | Array |

## Taxonomies

| Taxonomie | Description | Slug |
|-----------|-------------|------|
| `TypeDeBienTaxonomy` | Type de bien (appartement, maison, etc.) | `type-de-bien` |
| `VilleTaxonomy` | Ville du bien | `ville` |
| `EtatTaxonomy` | État du bien | `etat` |
| `DisponibiliteTaxonomy` | Disponibilité du bien | `disponibilite` |

## Classes principales

| Classe | Namespace | Description |
|--------|-----------|-------------|
| `Plugin` | `UpImmo\Core` | Classe principale du plugin (singleton) |
| `BienPostType` | `UpImmo\PostTypes` | Définition du type de contenu "Bien" |
| `ImportManager` | `UpImmo\Import` | Gestionnaire d'importation (singleton) |
| `CSVImportStrategy` | `UpImmo\Import\Strategies` | Stratégie d'importation CSV |
| `ContentFilters` | `UpImmo\Filters` | Filtres de contenu pour le traitement des données |
| `CronManager` | `UpImmo\Core` | Gestionnaire des tâches planifiées |
| `GutenbergManager` | `UpImmo\Core` | Gestionnaire des blocs Gutenberg |

## Hooks et filtres

| Hook | Type | Description |
|------|------|-------------|
| `plugins_loaded` | Action | Initialisation du plugin |
| `init` | Action | Enregistrement des types de contenu et taxonomies |
| `admin_enqueue_scripts` | Action | Chargement des scripts admin |
| `wp_ajax_up_immo_import` | Action | Gestion de l'import AJAX |
| `save_post_bien` | Action | Sauvegarde des données de bien |
| `manage_bien_posts_columns` | Filtre | Personnalisation des colonnes d'administration |
| `up_immo_clear_logs` | Action | Nettoyage des logs d'importation |

## Événements CRON

| Événement | Fréquence | Description |
|-----------|-----------|-------------|
| `up_immo_import_cron` | Quotidien | Importation automatique des biens |
| `up_immo_clear_logs` | Ponctuel | Nettoyage des logs après importation |