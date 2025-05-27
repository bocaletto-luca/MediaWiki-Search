<?php
// index.php
// Questo file PHP funge da contenitore per l’app: la logica è tutta in HTML, CSS e JavaScript.
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>MediaWiki Full Article Search App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Includo Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  
  <style>
    /* Temi Day / Night */
    body.day {
      background-color: #f8f9fa;
      color: #212529;
    }
    body.night {
      background-color: #212529;
      color: #f8f9fa;
    }
    
    /* Header e Footer */
    header, footer {
      background-color: #333;
      color: #fff;
      padding: 15px;
      text-align: center;
    }
    
    /* Contenitore principale */
    .container-main {
      max-width: 960px;
      margin: 20px auto;
      padding: 15px;
    }
    
    /* Form di ricerca */
    #searchForm {
      margin-bottom: 30px;
    }
    
    /* Card dei risultati */
    .result-item {
      margin-bottom: 20px;
      padding: 20px;
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .result-item h3 {
      margin-bottom: 15px;
    }
    .result-item p {
      font-size: 0.95rem;
    }
    .result-item img {
      max-width: 100%;
      height: auto;
      margin-bottom: 15px;
      border-radius: 0.25rem;
    }
    
    /* Spinner */
    .spinner {
      margin: 50px auto;
      display: block;
    }
    
    /* Messaggio di errore */
    #errorMessage {
      color: #d9534f;
      font-weight: bold;
      text-align: center;
      margin-bottom: 15px;
    }
    
    /* Modal: garantire uno scroll interno se il contenuto è lungo */
    .modal-body {
      max-height: 70vh;
      overflow-y: auto;
    }
    
    /* Footer */
    footer {
      margin-top: 40px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body class="day">
  <!-- Header -->
  <header>
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="m-0">MediaWiki Full Article Search</h1>
      <!-- Toggle per tema -->
      <div>
        <label for="themeToggle" class="form-check-label me-2">Tema</label>
        <input type="checkbox" id="themeToggle" class="form-check-input">
        <span id="themeText">Day</span>
      </div>
    </div>
  </header>
  
  <!-- Contenitore principale -->
  <div class="container-main">
    <!-- Form di Ricerca -->
    <form id="searchForm" class="mb-4">
      <div class="row g-3 align-items-center">
        <!-- Termini di ricerca -->
        <div class="col-md-5">
          <input type="text" id="searchQuery" class="form-control" placeholder="Inserisci termine di ricerca..." required>
        </div>
        <!-- Selezione lingua -->
        <div class="col-md-2">
          <select id="languageSelect" class="form-select">
            <option value="en" selected>English</option>
            <option value="it">Italiano</option>
            <option value="de">Deutsch</option>
            <option value="fr">Français</option>
            <option value="es">Español</option>
          </select>
        </div>
        <!-- Opzioni visualizzazione -->
        <div class="col-md-3">
          <div class="form-check">
            <input type="checkbox" id="showImages" class="form-check-input" checked>
            <label for="showImages" class="form-check-label">Mostra Immagini</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="showVideos" class="form-check-input" checked>
            <label for="showVideos" class="form-check-label">Mostra Video</label>
          </div>
          <div class="form-check">
            <input type="checkbox" id="showText" class="form-check-input" checked>
            <label for="showText" class="form-check-label">Mostra Testo</label>
          </div>
        </div>
        <!-- Bottone di ricerca -->
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Cerca</button>
        </div>
      </div>
    </form>
    
    <!-- Messaggio di errore -->
    <p id="errorMessage"></p>
    
    <!-- Container per i risultati -->
    <div id="resultContainer"></div>
  </div>
  
  <!-- Footer -->
  <footer>
    <p>&copy; <?php echo date("Y"); ?> MediaWiki Full Article Search App</p>
  </footer>
  
  <!-- Modale per visualizzare l'articolo completo -->
  <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailModalLabel"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body" id="modalBodyContent">
          <!-- Il contenuto dell'articolo verrà caricato qui -->
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap Bundle JS (include Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Gestione del tema Day/Night
    const themeToggle = document.getElementById("themeToggle");
    const themeText = document.getElementById("themeText");
    themeToggle.addEventListener("change", () => {
      if (themeToggle.checked) {
        document.body.classList.remove("day");
        document.body.classList.add("night");
        themeText.textContent = "Night";
      } else {
        document.body.classList.remove("night");
        document.body.classList.add("day");
        themeText.textContent = "Day";
      }
    });
    
    // Gestione del form di ricerca e variabili globali
    const searchForm = document.getElementById("searchForm");
    const resultContainer = document.getElementById("resultContainer");
    const errorMessage = document.getElementById("errorMessage");
    
    searchForm.addEventListener("submit", function(e) {
      e.preventDefault();
      resultContainer.innerHTML = "";
      errorMessage.textContent = "";
      
      const query = document.getElementById("searchQuery").value.trim();
      const lang = document.getElementById("languageSelect").value;
      const showImages = document.getElementById("showImages").checked;
      const showVideos = document.getElementById("showVideos").checked;
      const showText = document.getElementById("showText").checked;
      
      if(query === "") return;
      
      performSearch(query, lang, showImages, showVideos, showText);
    });
    
    // Funzione per eseguire la ricerca usando l'API di Wikipedia tramite il metodo query (list=search)
    async function performSearch(keyword, lang, showImages, showVideos, showText) {
      resultContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner" role="status"><span class="visually-hidden">Caricamento...</span></div></div>';
      const searchUrl = `https://${lang}.wikipedia.org/w/api.php?origin=*&action=query&list=search&format=json&srsearch=${encodeURIComponent(keyword)}`;
      
      try {
        const response = await fetch(searchUrl);
        if (!response.ok) {
          throw new Error("Errore nella risposta di rete");
        }
        const data = await response.json();
        displaySearchResults(data, lang, showImages, showVideos, showText);
      } catch (error) {
        console.error(error);
        resultContainer.innerHTML = "";
        errorMessage.textContent = "Si è verificato un errore durante la ricerca.";
      }
    }
    
    // Funzione per visualizzare i risultati della ricerca
    function displaySearchResults(data, lang, showImages, showVideos, showText) {
      resultContainer.innerHTML = "";
      if (!data.query || !data.query.search || data.query.search.length === 0) {
        resultContainer.innerHTML = "<p class='text-center'>Nessun risultato trovato.</p>";
        return;
      }
      
      data.query.search.forEach(item => {
        const title = item.title;
        const snippet = item.snippet; // snippet fornito dall'API, spesso contenente tag HTML
        let cardHTML = `<div class="result-item">
                          <h3>${escapeHtml(title)}</h3>
                          <p>${snippet}...</p>
                          <button class="btn btn-sm btn-secondary" onclick="loadFullArticle('${encodeURIComponent(title)}', '${lang}', ${showImages}, ${showVideos}, ${showText})">Leggi di più</button>
                        </div>`;
        resultContainer.innerHTML += cardHTML;
      });
    }
    
    // Funzione per caricare l'articolo completo tramite action=parse della MediaWiki API
    async function loadFullArticle(titleEncoded, lang, showImages, showVideos, showText) {
      const title = decodeURIComponent(titleEncoded);
      // Mostriamo subito la modale con un messaggio di caricamento
      openDetailModal(title, `<p class="text-center">Caricamento articolo completo...</p>`);
      
      const parseUrl = `https://${lang}.wikipedia.org/w/api.php?origin=*&action=parse&page=${encodeURIComponent(title)}&prop=text&format=json`;
      
      try {
        const response = await fetch(parseUrl);
        if (!response.ok) {
          throw new Error("Errore nel recupero dell'articolo");
        }
        const data = await response.json();
        let articleHTML = data.parse.text["*"];
        
        // Applichiamo le opzioni selezionate: se non si vogliono immagini o video, li rimuoviamo
        if (!showImages) {
          articleHTML = articleHTML.replace(/<img[\s\S]*?\/?>/gi, "");
        }
        if (!showVideos) {
          articleHTML = articleHTML.replace(/<iframe[\s\S]*?<\/iframe>/gi, "");
        }
        if (!showText) {
          articleHTML = articleHTML.replace(/<p[\s\S]*?<\/p>/gi, "");
        }
        
        openDetailModal(title, articleHTML);
      } catch (error) {
        console.error(error);
        openDetailModal(title, `<p class="text-center text-danger">Errore nel recupero dell'articolo completo.</p>`);
      }
    }
    
    // Funzione per aprire la modale e mostrare il contenuto passato
    function openDetailModal(title, contentHTML) {
      const modalTitle = document.getElementById("detailModalLabel");
      const modalBody = document.getElementById("modalBodyContent");
      modalTitle.textContent = title;
      modalBody.innerHTML = contentHTML;
      
      const detailModalElem = document.getElementById("detailModal");
      const detailModal = new bootstrap.Modal(detailModalElem);
      detailModal.show();
    }
    
    // Listener per l'evento "hidden.bs.modal" per rimuovere eventuali residui
    const detailModalElem = document.getElementById("detailModal");
    detailModalElem.addEventListener("hidden.bs.modal", function () {
      // Pulizia del contenuto della modale
      document.getElementById("modalBodyContent").innerHTML = "";
      // Rimozione forzata delle classi residue sul body
      document.body.classList.remove("modal-open");
      // Rimozione del backdrop residuo
      const backdrops = document.getElementsByClassName("modal-backdrop");
      while(backdrops[0]){
        backdrops[0].parentNode.removeChild(backdrops[0]);
      }
      // Se l'istanza della modale esiste, la disfacciamo per evitare conflitti futuri
      const modalInstance = bootstrap.Modal.getInstance(detailModalElem);
      if(modalInstance) {
        modalInstance.dispose();
      }
    });
    
    // Funzione per effettuare l'escape dei caratteri HTML per prevenire XSS
    function escapeHtml(text) {
      if (typeof text !== "string") return text;
      const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "'": "&#039;"
      };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
  </script>
</body>
</html>
