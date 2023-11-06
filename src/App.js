/**
 * @module App
 */
export default class App {
	/**
	 * Méthode principale. Sera appelée après le chargement de la page.
	 */
	static main() {
		var q = "sex";
		var app = document.getElementById("app");
		this.chargerJson(`https://www.thecocktaildb.com/api/json/v1/1/search.php?s=${q}`).then(donnees => {
			app.appendChild(this.html_listeCocktails(donnees.drinks));
		});
		var formRecherche = document.forms.recherche;
		this.chargerJson(`https://www.thecocktaildb.com/api/json/v1/1/list.php?c=list`).then(donnees => {
			formRecherche.appendChild(this.html_dataList(donnees.drinks));
		});
		formRecherche.addEventListener("submit", e => {
			e.preventDefault();
			var q = formRecherche.q.value;
			this.chargerJson(`https://www.thecocktaildb.com/api/json/v1/1/search.php?s=${q}`).then(donnees => {
				var vieux = document.querySelector(".liste-cocktails");
				vieux.replaceWith(this.html_listeCocktails(donnees.drinks));
			});
		})
	}
	
	static html_dataList(liste) {
		var resultat = document.createElement("datalist");
		resultat.id = "liste-categories";
		for (let i = 0; i < liste.length; i++) {
			const objCategorie = liste[i];
			var option = resultat.appendChild(document.createElement("option"));
			option.value = objCategorie.strCategory;
		}
		return resultat;
	}

	static html_listeCocktails(tCocktails) {
		var resultat = document.createElement("div");
		resultat.classList.add("liste-cocktails");
		for (let i = 0; i < tCocktails.length; i += 1) {
			const objCocktail = tCocktails[i];
			resultat.appendChild(this.html_cocktail(objCocktail));
		}

		return resultat;
	}
	static html_cocktail(objCocktail) {
		var resultat = document.createElement("div");
		resultat.appendChild(this.html_titreCocktail(objCocktail.strDrink, objCocktail.idDrink));
		resultat.appendChild(this.html_imageCocktail(objCocktail.strDrinkThumb));
		return resultat;
	}
	static html_titreCocktail(titre, id) {
		var resultat = document.createElement("h2");
		var a = resultat.appendChild(document.createElement("a"));
		a.href = `drink.html?id=${id}`;
		a.innerHTML = titre;
		return resultat;
	}
	static html_imageCocktail(url) {
		var resultat = document.createElement("figure");
		var img = resultat.appendChild(document.createElement("img"));
		img.src = url;
		return resultat;
	}

	static chargerJson(url) {
		return new Promise(resolve => {
			var xhr = new XMLHttpRequest();
			xhr.open("get", url);
			xhr.responseType = "json";
			xhr.addEventListener("load", e => {
				resolve(e.target.response); // Retourne les données
			});
			xhr.send();
		});
	}
}
