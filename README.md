# up-immo

## Changelog

### 1.4.0 - 2026-04-01
- **Settings** : Ajout d'une page de paramètres dans l'administration (Biens > Settings)
- **Suppression** : Option pour supprimer automatiquement les images attachées lors de la suppression d'un bien
- **Import** : Ajout du champ 'type' comme métadonnée lors de l'import CSV
- **Import** : Conversion des balises <br> en sauts de ligne réels dans la description pour un meilleur affichage dans le textarea

### 1.3.0 - 2026-03-30
- **Correctif Critique** : Résolution de l'erreur fatale "Class AdminAjax not found" via un chargement robuste des classes.
- **Robustesse** : Passage aux chemins absolus (`plugin_dir_path`) pour l'inclusion des fichiers sources.
- **Maintenance** : Nettoyage des logs de debug et optimisation du constructeur principal.
- **Standardisation** : Correction de l'implémentation de `CSVImportStrategy` pour respecter strictement son interface.

### 1.2.0 - 2026-03-30
- **Import CSV** : Ajout des métadonnées DPE manquantes (`energie`, `energie_lettre`, `ges`, `ges_lettre`, `dpe_date`)
- **Metabox** : Ajout des champs DPE éditables dans l'admin (énergie, lettre énergie, GES, lettre GES, date DPE)

## Import automatique via navigateur

Le plugin permet désormais de déclencher l'import depuis n'importe quel navigateur (pratique lorsqu'un cron serveur n'est pas disponible) :

1. Dans l'admin WordPress, ouvrez **Biens → Import**.
2. Renseignez **Chemin de fichier par défaut** puis cochez **Déclenchement via navigateur**.
3. Notez l'URL sécurisée affichée (elle contient un token unique). Vous pouvez la visiter manuellement ou l'ajouter à un service de cron externe.
4. Facultatif : cochez *Régénérer le token* si l'URL doit être invalidée.

Chaque visite sur cette URL lance l'import CSV avec le chemin configuré et renvoie un JSON indiquant le résultat.