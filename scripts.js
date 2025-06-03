

/************************************************************/
/*          Menu functions                       */
/***********************************************************/

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
      afficherAutresOptions();
      break;
    case "5":
      afficherAutresOptions();
      break;
    default:
      alert("Choix invalide. Veuillez sélectionner un service valide.");
  }
}


/************************************************************/
/*          classCss Hidden Container functions                       */
/***********************************************************/

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

function afficherFormulaireTransaction(type) {
  document.querySelector(".containerMenuAccount").classList.add("hidden");
  document.querySelector(".containerTransfert").classList.remove("hidden");

  const champCategorie = document.getElementById("id_categorie");
  champCategorie.value = type.charAt(0) + type.slice(1);
  

  document.getElementById("id_montant").value = "";
  document.getElementById("id_description").value = "";
}


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
        console.error(error,'iciciciciicici')
        alert("Erreur Ajax : " + error);
      },
    });
  
}


function retourMenu() {
  document
    .querySelectorAll(
      ".containerBalance, .containerTransfert, .containerPaiementFacture, .containerChangeCode, .containerTransactions"
    )
    .forEach((container) => container.classList.add("hidden"));
  document.querySelector(".containerMenuAccount").classList.remove("hidden");
}

/************************************************************/
/*           Ajax functions                       */
/***********************************************************/

function validerCompte() {
  var numeroCompte = document.getElementById("number").value;
  var codeCompte = document.getElementById("code").value;

  if (!numeroCompte || !codeCompte) {
    alert("Veuillez remplir tous les champs.");
    return;
  }

  if (codeCompte !== "#9999#") {
    alert("Tu as saisi le mauvais code pour accéder au menu");
    return;
  }

  $.ajax({
    url: 'php/validate_account.php',
    type: 'POST',
    dataType: 'json',
    data: { numeroCompte: numeroCompte },
    success: function(response) {
      console.log("Réponse reçue :", response);

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




function fetchTransactions(numeroCompte) {
    $.ajax({
        url: 'php/fetch_transactions.php',
        type: 'POST',
        data: { numeroCompte: numeroCompte },
        dataType: 'json',
        success: function(response) {
            let html = '';
            response.transactions.forEach(t => {
                html += `<p>${t.date} - ${t.montant}F (${t.categorie})</p>`;
            });
            document.getElementById("transactionsList").innerHTML = html;
        }
    });
}




function afficherStatistiques() {
    const numeroCompte = $('#numeroCompteDisplay').text().trim();
    $.ajax({
        url: 'php/fetch_stats.php',
        type: 'POST',
        data: { numeroCompte: numeroCompte },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`STATISTIQUES\n
                    Solde: ${response.solde}F\n
                    Revenus: +${response.revenus}F\n
                    Dépenses: -${response.depenses}F`);
            }
        }
    });
}