module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './public/js/**/*.js'], // Chemin vers les fichiers HTML et JS qui contiennent les classes utilisées
  css: ['./public/css/daisyUi.min.css'], // Chemin vers ton fichier CSS principal
  output: './purged/',
};
