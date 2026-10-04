# Elodie CMS 1.00

Elodie CMS 1.00 est la nouvelle version modernisée de UAG CMS, réécrite progressivement pour PHP 8 et SQLite. Elle conserve l’héritage et les données du CMS d’origine tout en adoptant une nouvelle identité et des mécanismes de sécurité actualisés.

Elodie CMS est un moteur de blog que j’ai créé à l’origine pour disposer d’une solution maison adaptée à mes besoins. Je reste l’autrice originale du projet. Le code a été modernisé avec l’aide de GitHub Copilot, un outil d’assistance au développement.

Je suis également la créatrice de BlockColor pour Luanti. Mes projets ont pour vocation le partage. J’utilise aussi des outils d’IA pour créer de la musique. Mon regard sur l’intelligence artificielle est nuancé : je ne suis ni « pour » ni « contre ». Ma santé et mon énergie ne me permettent pas toujours de tout réaliser seule, et je reconnais que l’IA peut être un outil complémentaire utile, sans remplacer l’intention ni la responsabilité de la personne qui crée.

## Installation et migration

Cette version conserve l’interface existante et migre automatiquement les réglages et articles historiques vers une base SQLite au premier démarrage. Les anciens fichiers `admin/configuration.txt` et `news.php` restent en place comme copie de récupération ; toutes les nouvelles modifications sont enregistrées dans `data/elodie-cms.sqlite`. Si `data/uag.sqlite` existe déjà, sa base est copiée vers le nouveau nom sans supprimer l’original.

- PHP 8.2 ou supérieur avec `PDO_SQLite`, `dom` et `fileinfo` (utiliser une version encore maintenue).
- Le serveur web doit pouvoir créer et écrire dans `data/` et `images/`. La base est exclue de Git et `data/.htaccess` bloque son accès HTTP. Avec Nginx ou un autre serveur, configurez aussi explicitement le refus d’accès HTTP à `data/`.
- Pour Apache, activer `mod_rewrite` et autoriser les règles `.htaccess` (`AllowOverride`). Elles protègent également les articles historiques et empêchent l’exécution de scripts dans `images/`.
- En production, activez HTTPS pour tout le site et configurez une règle équivalente de refus d’accès au dossier `data/` si le serveur n’applique pas les fichiers `.htaccess`. Ne faites pas confiance à `X-Forwarded-Proto` sans proxy inverse explicitement configuré et maîtrisé.
- Faites une sauvegarde du dossier du site avant la première ouverture : la migration est transactionnelle et ne supprime pas les fichiers sources. Pour sauvegarder `data/elodie-cms.sqlite`, utilisez la commande `.backup` de SQLite ou arrêtez temporairement le site avant de copier la base ; restaurez ensuite une copie et vérifiez qu’elle s’ouvre avant de compter sur elle.
- Pour une nouvelle installation, ouvrir `install.php` une seule fois et choisir un mot de passe d’au moins 12 et d’au plus 72 octets (limite du hachage bcrypt utilisé par PHP par défaut). Dans les réglages, laisser le champ du mot de passe vide le conserve.
- Après une connexion avec le mot de passe, le CMS guide l’administratrice ou l’administrateur dans l’activation d’une application d’authentification TOTP compatible avec Google Authenticator. Enregistrez les codes de secours affichés : ils ne sont montrés qu’une seule fois. La page d’administration permet d’en générer un nouveau lot.
- Les mots de passe SHA-1 des anciennes installations de 72 octets ou moins sont migrés vers un hachage moderne à la première connexion réussie. Pour un ancien mot de passe plus long, connectez-vous puis définissez-en un nouveau dans les réglages.
- Les articles conservent leur mise en forme, mais leur HTML est assaini à l’enregistrement et à l’affichage. Les émoticônes historiques sont converties en emojis Unicode actuels ; les nouveaux emojis s’affichent directement avec la police emoji du système.
- L’affichage utilise les polices déjà disponibles sur l’appareil, sans chargement de police depuis un service externe.
- Les scripts, iframes et SVG téléversés ne sont pas acceptés.
- Les commentaires internes sont désactivés par défaut. Vous pouvez les activer dans les réglages ; les nouveaux messages sont conservés dans SQLite et doivent être approuvés depuis l’administration avant publication. Leur soumission est limitée afin de réduire le spam.
- Depuis l’administration, vous pouvez demander une vérification des dernières versions publiées sur GitHub. Cette vérification manuelle ne télécharge et n’installe aucun fichier automatiquement ; si une version plus récente existe, le CMS affiche un lien vers ses notes de version.

## Licence

Elodie CMS est distribué sous licence MIT. Voir le fichier `LICENSE`. Les bibliothèques et ressources tierces intégrées conservent leurs licences et avis de copyright respectifs.

## Remarque de sécurité

Le projet est ancien et sa mise à niveau est progressive. Cette version renforce plusieurs failles historiques, mais ne constitue pas une garantie d’audit de sécurité exhaustif.
