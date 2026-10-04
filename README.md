# Elodie CMS 1.02 Meow

Elodie CMS 1.02 Meow poursuit la modernisation de UAG CMS, amorcée avec Elodie CMS 1.00, en faisant évoluer progressivement le projet vers PHP 8 et SQLite. Cette version ajoute des pages autonomes et des menus configurables, tout en conservant l’héritage et les données du CMS d’origine.

Elodie CMS est un moteur de blog que j’ai créé à l’origine pour disposer d’une solution maison adaptée à mes besoins. Je reste l’autrice originale du projet. Le code a été modernisé avec l’aide de GitHub Copilot, un outil d’assistance au développement.

Je suis également la créatrice de BlockColor pour Luanti. Mes projets ont pour vocation le partage. J’utilise aussi des outils d’IA pour créer de la musique. Mon regard sur l’intelligence artificielle est nuancé : je ne suis ni « pour » ni « contre ». Ma santé et mon énergie ne me permettent pas toujours de tout réaliser seule, et je reconnais que l’IA peut être un outil complémentaire utile, sans remplacer l’intention ni la responsabilité de la personne qui crée.

## Installation et migration

Cette version conserve l’interface existante et migre automatiquement les réglages et articles historiques vers une base SQLite au premier démarrage. Les anciens fichiers `admin/configuration.txt` et `news.php` restent en place comme copie de récupération ; toutes les nouvelles modifications sont enregistrées dans `data/elodie-cms.sqlite`. Si `data/uag.sqlite` existe déjà, sa base est copiée vers le nouveau nom sans supprimer l’original.

- PHP 8.2 ou supérieur avec `PDO_SQLite`, `dom` et `fileinfo` (utiliser une version encore maintenue). L’extension `GD` est nécessaire pour téléverser des images : le CMS les décode puis les réencode pour retirer les données cachées et bloquer les images polyglottes. `ZipArchive`, `allow_url_fopen` et un accès HTTPS sortant vers GitHub sont requis uniquement pour installer les mises à jour depuis l’administration.
- Le serveur web doit pouvoir créer et écrire dans `data/` et `images/`. La base est exclue de Git et `data/.htaccess` bloque son accès HTTP. Avec Nginx ou un autre serveur, configurez aussi explicitement le refus d’accès HTTP à `data/`.
- Pour Apache, activer `mod_rewrite` et autoriser les règles `.htaccess` (`AllowOverride`). Elles protègent également les articles historiques et empêchent l’exécution de scripts dans `images/`.
- En production, activez HTTPS pour tout le site et configurez une règle équivalente de refus d’accès au dossier `data/` si le serveur n’applique pas les fichiers `.htaccess`. Ne faites pas confiance à `X-Forwarded-Proto` sans proxy inverse explicitement configuré et maîtrisé.
- Faites une sauvegarde du dossier du site avant la première ouverture : la migration est transactionnelle et ne supprime pas les fichiers sources. Pour sauvegarder `data/elodie-cms.sqlite`, utilisez la commande `.backup` de SQLite ou arrêtez temporairement le site avant de copier la base ; restaurez ensuite une copie et vérifiez qu’elle s’ouvre avant de compter sur elle.
- Pour une nouvelle installation, ouvrir `install.php` une seule fois et choisir un mot de passe d’au moins 12 et d’au plus 72 octets (limite du hachage bcrypt utilisé par PHP par défaut). Dans les réglages, laisser le champ du mot de passe vide le conserve.
- Après une connexion avec le mot de passe, le CMS guide l’administratrice ou l’administrateur dans l’activation d’une application d’authentification TOTP compatible avec Google Authenticator. Enregistrez les codes de secours affichés : ils ne sont montrés qu’une seule fois. La page d’administration permet d’en générer un nouveau lot.
- Les mots de passe SHA-1 des anciennes installations de 72 octets ou moins sont migrés vers un hachage moderne à la première connexion réussie. Pour un ancien mot de passe plus long, connectez-vous puis définissez-en un nouveau dans les réglages.
- Les articles conservent leur mise en forme, mais leur HTML est assaini à l’enregistrement et à l’affichage. Les émoticônes historiques sont converties en emojis Unicode actuels ; les nouveaux emojis s’affichent directement avec la police emoji du système.
- L’affichage utilise les polices déjà disponibles sur l’appareil, sans chargement de police depuis un service externe.
- Le site public et l’administration utilisent une interface responsive adaptée aux téléphones, tablettes et ordinateurs. L’administration possède une navigation persistante sans fenêtres flottantes ni iframes ; les articles, médias, commentaires et réglages s’ouvrent comme des pages normales.
- L’éditeur d’articles propose un mode visuel, Markdown ou BBCode par article, des outils de mise en forme, l’insertion d’images déjà téléversées et une version texte de secours si JavaScript est désactivé. Le HTML reste assaini côté serveur avant enregistrement et à l’affichage.
- Les pages autonomes, dont la page « À propos », sont gérées séparément des articles. La page Apparence permet de régler le titre, la description, les couleurs, le logo, l’image d’arrière-plan et le favicon ; les images peuvent être choisies dans les médias envoyés ou renseignées par URL.
- La couleur d’arrière-plan choisie dans Apparence s’affiche sans voile gris qui en altère le rendu.
- Les éléments du menu peuvent cibler une URL, un article ou une page et être réordonnés depuis les réglages. Le flux RSS reste disponible sans apparaître dans le menu principal.
- Les pages d’installation, de connexion et de sécurité reprennent les mêmes styles adaptatifs et présentent leurs formulaires dans des panneaux lisibles sur petit écran.
- Les libellés historiques du CMS et les dates des articles sont disponibles en français, anglais, espagnol, néerlandais, allemand, italien et portugais.
- Les interfaces récentes (installation, connexion, administration, sécurité et blog public) utilisent également des traductions dédiées dans ces sept langues.
- Le site public présente maintenant les articles sous la forme d’un blog classique, avec une page d’accueil éditoriale, des archives paginées, des pages d’article, un lien RSS et une section « À propos » ; l’ancienne interface publique façon système de bureau est retirée.
- Les scripts, iframes et SVG téléversés ne sont pas acceptés.
- Les commentaires internes sont désactivés par défaut. Vous pouvez les activer dans les réglages ; les nouveaux messages sont conservés dans SQLite et doivent être approuvés depuis l’administration avant publication. Leur soumission est limitée afin de réduire le spam.
- Depuis l’administration, vous pouvez vérifier les versions stables publiées sur GitHub et confirmer explicitement l’installation d’une version plus récente. Le CMS conserve une sauvegarde des fichiers remplacés dans `data/updates/` et préserve la base SQLite, les images, les réglages et les articles historiques. Vérifiez les notes de version et effectuez une sauvegarde complète avant l’installation ; le serveur doit autoriser l’écriture des fichiers du CMS.

## Licence

Elodie CMS est distribué sous licence MIT. Voir le fichier `LICENSE`. Les bibliothèques et ressources tierces intégrées conservent leurs licences et avis de copyright respectifs.

## Remarque de sécurité

Le projet est ancien et sa mise à niveau est progressive. Cette version renforce plusieurs failles historiques, mais ne constitue pas une garantie d’audit de sécurité exhaustif.
