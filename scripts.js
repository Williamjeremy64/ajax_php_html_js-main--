/************************************************************/
/*          Gestion du menu principal                       */
/***********************************************************/
/**
 * Fonction principale qui gère les choix du menu
 * 1: Voir le solde
 * 2: Ajouter un revenu
 * 3: Ajouter une dépense
 * 4: Voir les 5 dernières transactions
 * 5: Voir les statistiques
 */
function menu() {
  var choixService = document.getElementById("serviceInput").value;
  switch (choixService) {
    case "1":
      var numeroCompte = document.getElementById("numeroCompteDisplay").textContent.trim();
      fetchAndDisplaySolde(numeroCompte);
      break;
    case "2":
      afficherFormulaireTransaction("revenu");
      break;
    case "3":
      afficherFormulaireTransaction("depense");
      break;
    case "4":
      afficherTransactions();
      break;
    case "5":
      afficherStatsContainer();
      break;
    default:
      alert("Choix invalide. Veuillez sélectionner un service valide.");
  }
}

/************************************************************/
/*          Gestion de l'affichage des conteneurs           */
/***********************************************************/
/**
 * Cache tous les conteneurs pour éviter les superpositions
 */
function cacherTousLesContainers() {
  var containers = document.querySelectorAll(".containerMenuAccount, .containerAutresOptions, .containerChangeCode, .containerTransactions");
  containers.forEach(container => {
    container.classList.add("hidden");
  });
}

function afficherChangeCodeForm() {
  cacherTousLesContainers();
  document.querySelector(".containerChangeCode").classList.remove("hidden");
}

function afficherTransactions() {
  cacherTousLesContainers();
  document.querySelector(".containerTransactions").classList.remove("hidden");
  fetchTransactions(document.getElementById("numeroCompteDisplay").textContent.trim());
}

function afficherSolde() {
  document.querySelector(".containerMenuAccount").classList.add("hidden");
  document.querySelector(".containerBalance").classList.remove("hidden");
}

/**
 * Affiche le formulaire de transaction (revenu ou dépense)
 * @param {string} type - Le type de transaction ('revenu' ou 'depense')
 */
function afficherFormulaireTransaction(type) {
  document.querySelector(".containerMenuAccount").classList.add("hidden");
  document.querySelector(".containerTransfert").classList.remove("hidden");

  const champCategorie = document.getElementById("id_categorie");
  champCategorie.value = type.charAt(0) + type.slice(1);
  
  document.getElementById("id_montant").value = "";
  document.getElementById("id_description").value = "";
}

/************************************************************/
/*          Gestion des transactions                        */
/***********************************************************/
/**
 * Ajoute une nouvelle transaction (revenu ou dépense)
 * Vérifie les champs requis et met à jour le solde
 */
function ajouterTransaction() {
  const numeroCompte = document.getElementById("numeroCompteDisplay").textContent.trim();
  const montant = document.getElementById("id_montant").value.trim();
  const description = document.getElementById("id_description").value.trim();
  const type = document.getElementById("id_categorie").value.trim();

  if (!montant || !description) {
    alert("Tous les champs sont requis.");
    return;
  }

  $.ajax({
    url: "php/add_transaction.php",
    type: "POST",
    data: {
      numeroCompte: numeroCompte,
      type: type,
      montant: montant,
      description: description,
    },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        alert(response.message + "\nNouveau solde : " + response.nouveauSolde);
        if (confirm("Retour au menu ?")) {
          menu();
        }
      } else {
        alert("Erreur : " + response.message);
      }
    },
    error: function (_xhr, _status, error) {
      console.error(error);
      alert("Erreur Ajax : " + error);
    },
  });
}

/**
 * Retourne au menu principal en masquant les autres conteneurs
 */
function retourMenu() {
  document
    .querySelectorAll(
      ".containerBalance, .containerTransfert, .containerPaiementFacture, .containerChangeCode, .containerTransactions"
    )
    .forEach((container) => container.classList.add("hidden"));
  document.querySelector(".containerMenuAccount").classList.remove("hidden");
}

/************************************************************/
/*          Gestion de l'authentification                    */
/***********************************************************/
/**
 * Valide les identifiants de connexion
 * Vérifie le numéro de compte et le code
 */
function validerCompte() {
  var numeroCompte = document.getElementById("number").value;
  var codeCompte = document.getElementById("code").value;

  if (!numeroCompte || !codeCompte) {
    alert("Veuillez remplir tous les champs.");
    return;
  }

  $.ajax({
    url: 'php/validate_account.php',
    type: 'POST',
    dataType: 'json',
    data: { 
      numeroCompte: numeroCompte,
      code: codeCompte 
    },
    success: function(response) {
      if (response.success) {
        $('#numeroCompteDisplay').text(numeroCompte);
        $('.containerCheckAccount').addClass('hidden');
        $('.containerMenuAccount').removeClass('hidden');
      } else {
        alert("Erreur : " + (response.message || "Réponse inconnue."));
      }
    },
    error: function(xhr, status, error) {
      console.error("Erreur Ajax :", xhr.responseText);
      alert("Erreur lors de la requête : " + status + " - " + error);
    }
  });
}

/************************************************************/
/*          Gestion du solde et des transactions            */
/***********************************************************/
/**
 * Récupère et affiche le solde du compte
 * @param {string} numeroCompte - Le numéro de compte
 */
function fetchAndDisplaySolde(numeroCompte) {
  $.ajax({
    url: 'php/fetch_balance.php',
    type: 'POST',
    dataType: 'json',
    data: { numeroCompte: numeroCompte },
    success: function(response) {
      if (response.success) {
        $('#soldeCompte').text(response.solde + ' FCFA');
        afficherSolde();
        $('#numeroCompteAffiche').text(numeroCompte);
      } else {
        alert("Erreur : " + response.message);
      }
    },
    error: function(_xhr, status, _error) {
      alert("Erreur lors de la récupération du solde : " + status);
    }
  });
}

/**
 * Récupère et affiche les 5 dernières transactions
 * @param {string} numeroCompte - Le numéro de compte
 */
function fetchTransactions(numeroCompte) {
  $.ajax({
    url: 'php/fetch_transactions.php',
    type: 'POST',
    data: { numeroCompte: numeroCompte },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        let html = '';
        if (response.transactions.length === 0) {
          html = '<p>Aucune transaction trouvée</p>';
        } else {
          response.transactions.forEach(t => {
            const montant = parseFloat(t.montant);
            const montantFormatted = new Intl.NumberFormat('fr-FR').format(montant);
            const signe = t.type === 'revenu' ? '+' : '-';
            html += `<p>${t.date} - ${t.type === 'revenu' ? 'Revenu' : 'Dépense'} : ${signe}${montantFormatted} FCFA</p>`;
          });
        }
        document.getElementById("transactionsList").innerHTML = html;
      } else {
        document.getElementById("transactionsList").innerHTML = 
          `<p>Erreur : ${response.message || 'Erreur inconnue'}</p>`;
      }
    },
    error: function(xhr, status, error) {
      console.error("Erreur Ajax :", xhr.responseText);
      document.getElementById("transactionsList").innerHTML = 
        `<p>Erreur lors de la récupération des transactions : ${error}</p>`;
    }
  });
}

/************************************************************/
/*          Gestion des statistiques                        */
/***********************************************************/
/**
 * Affiche le conteneur des statistiques
 */
function afficherStatsContainer() {
  cacherTousLesContainers();
  document.querySelector(".containerStats").classList.remove("hidden");
  fetchAndDisplayStats();
}

/**
 * Récupère et affiche les statistiques du compte
 * Affiche le solde, les revenus et les dépenses
 */
function fetchAndDisplayStats() {
  const numeroCompte = document.getElementById("numeroCompteDisplay").textContent.trim();
  $.ajax({
    url: 'php/fetch_stats.php',
    type: 'POST',
    data: { numeroCompte: numeroCompte },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        const html = `<p><b>Solde :</b> ${response.solde} FCFA</p>
                      <p><b>Revenus :</b> +${response.revenus} FCFA</p>
                      <p><b>Dépenses :</b> -${response.depenses} FCFA</p>`;
        document.getElementById("statsList").innerHTML = html;
      } else {
        document.getElementById("statsList").innerHTML = '<p>Erreur lors de la récupération des statistiques.</p>';
      }
    },
    error: function() {
      document.getElementById("statsList").innerHTML = '<p>Erreur Ajax lors de la récupération des statistiques.</p>';
    }
  });
}