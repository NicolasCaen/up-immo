# up-immo

## Changelog

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