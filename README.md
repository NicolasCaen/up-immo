# up-immo

## Import automatique via navigateur

Le plugin permet désormais de déclencher l'import depuis n'importe quel navigateur (pratique lorsqu'un cron serveur n'est pas disponible) :

1. Dans l'admin WordPress, ouvrez **Biens → Import**.
2. Renseignez **Chemin de fichier par défaut** puis cochez **Déclenchement via navigateur**.
3. Notez l'URL sécurisée affichée (elle contient un token unique). Vous pouvez la visiter manuellement ou l'ajouter à un service de cron externe.
4. Facultatif : cochez *Régénérer le token* si l'URL doit être invalidée.

Chaque visite sur cette URL lance l'import CSV avec le chemin configuré et renvoie un JSON indiquant le résultat.