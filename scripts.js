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
      fetchAndDisplaySolde();
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
  var containers = document.querySelectorAll(".containerMenuAccount, .containerAutresOptions, .containerChangeCode, .containerTransactions, .containerTransfert, .containerBalance, .containerStats");
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
  fetchTransactions();
}

function afficherSolde() {
  cacherTousLesContainers();
  document.querySelector(".containerBalance").classList.remove("hidden");
}

function afficherStatsContainer() {
  cacherTousLesContainers();
  document.querySelector(".containerStats").classList.remove("hidden");
  fetchAndDisplayStats();
}

/**
 * Affiche le formulaire de transaction (revenu ou dépense)
 * @param {string} type - Le type de transaction ('revenu' ou 'depense')
 */
function afficherFormulaireTransaction(type) {
  cacherTousLesContainers();
  document.querySelector(".containerTransfert").classList.remove("hidden");

  // Mettre à jour le titre et la catégorie en fonction du type
  const titre = type === 'revenu' ? 'Ajout du revenu' : 'Ajout de la dépense';
  document.querySelector('.containerTransfert h2').textContent = titre;
  
  // Définir la catégorie en fonction du type (1 pour Revenu, 2 pour Dépense)
  document.getElementById("id_categorie").value = type === 'revenu' ? '1' : '2';
  
  // Réinitialiser les champs
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
  const categorie = document.getElementById("id_categorie").value;
  const description = document.getElementById("id_description").value.trim();

  if (!montant || !description) {
    alert("Le montant et la description sont requis.");
    return;
  }

  $.ajax({
    url: "php/add_transaction.php",
    type: "POST",
    data: {
      numeroCompte: numeroCompte,
      montant: montant,
      categorie: categorie,
      description: description
    },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        alert(response.message);
        retourMenu();
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
  cacherTousLesContainers();
  document.querySelector(".containerMenuAccount").classList.remove("hidden");
}

/************************************************************/
/*          Gestion de l'authentification                    */
/***********************************************************/
/**
 * Valide les identifiants de connexion
 * Vérifie le numéro de téléphone et le code
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
 */
function fetchAndDisplaySolde() {
  $.ajax({
    url: 'php/fetch_balance.php',
    type: 'POST',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        $('#soldeCompte').text(response.solde + ' FCFA');
        afficherSolde();
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
 */
function fetchTransactions() {
  $.ajax({
    url: 'php/fetch_transactions.php',
    type: 'POST',
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
            const typeAffichage = t.type === 'revenu' ? 'Revenu' : 'Dépense';
            html += `<p>${t.date} - ${typeAffichage} : ${signe}${montantFormatted} FCFA<br><small>${t.description}</small></p>`;
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

/**
 * Récupère et affiche les statistiques du compte
 */
function fetchAndDisplayStats() {
  $.ajax({
    url: 'php/fetch_stats.php',
    type: 'POST',
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