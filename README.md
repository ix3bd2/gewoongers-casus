# Casus zaaglijst
Niveau: ~~makkelijk~~/gemiddeld/~~moeilijk~~]

## Lokaal installeren

Om deze applicatie lokaal op te zetten kan je de volgende stappen volgen:
1. Fork het project. Zie [hier](https://docs.github.com/en/get-started/quickstart/fork-a-repo) meer informatie over 
het forken van een project.
2. Doe `composer install` om alle benodigde packages van laravel te installeren. Heb je nog geen composer op 
jouw machine staan dan kan je [hier](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) lezen 
hoe je het kan installeren.
3. Maak een kopie van het `.env.example` bestand en noem het `.env`. 
4. Doe `php artisan serve` en de applicatie is dan te benaderen op localhost. Als je 
[laravel valet](https://laravel.com/docs/9.x/valet) heb kan je hem ook daarmee draaien. 
5. Als je naar `{{baseUrl}}/api/data` gaat zie je de huidige dataset. Het is een json output en het is 
bijvoorbeeld handig om hiervoor [Postman](https://www.postman.com/downloads/) te gebruiken.

## Waar kan ik beginnen?

Er is een controller aangemaakt genaamd `ProductionStateController`. In deze functie zit tot nu toe nog één functie
`index`. Deze functie pakt de json data uit `storage/data/ProductieStaat.json` en het returnt nu alle productiestaten.

Deze controller is jouw startpunt. Je mag natuurlijk alles doen wat jij denkt wat nodig is om op de oplossing te komen.
Dus denk jij dat het nodig is gebruik te maken van meerdere controllers of ga je gebruik maken van tests is dat zeker 
geen probleem. Nogmaals, je krijgt alle vrijheid.

Genoeg tekst. Je mag eindelijk aan de slag!

## Casus

In hal 3, waar al onze profielen staan opgeslagen, worden er dagelijks profielen gepickt die de productie van een hele 
dag moet kunnen bevoorraden. Een deur bestaat uit verschillende profielen. Een profiel wordt aangeduid met de letter 
`G` als prefix (dus wij hebben profielen G01 tot en met G72). Er zijn dus 72 verschillende profielen die gebruikt 
worden om onze deuren te maken. Elk profiel is ook in al onze 12 kleuren beschikbaar.

De orderpicker kijkt in de huidige situatie naar alle deuren die die dag gemaakt moeten worden en op basis daarvan 
maakt diegene een inschatting wat er aan profielen nodig is die dag. Dit is een proces wat inefficiënt is en enorm 
veel tijd kost om uit te rekenen wat er op een dag nodig is.

### Wat is de oplossing?

Wat de orderpicker enorm kan helpen is een lijst met hoeveel profielen in welke kleur diegene die dag moet 
picken om aan de vraag te kunnen voldoen van de productie. 

Om uit te kunnen rekenen hoeveel profielen er nodig zijn maken wij gebruik van een externe API 
[optiCutter](https://www.opticutter.com/public/doc/api#introduction). Die berekend aan de hand van elk profiel hoeveel 
er nodig zijn op basis van de afmetingen en de hoeveelheid profielen er van die maat gezaagd moeten gaan worden. 
Deze API neemt ook gelijk de efficiëntste manier van zagen mee. Dit betekent dat wij ook gelijk de "waste" kunnen 
verminderen met deze oplossing! 

De input die wij nodig hebben om deze API te vullen is het onderstaande object. 

```json
{
    "PROFIELKLEUR: RAL 9005 Gitzwart": {
        "G01": [
            {
                "length": 1987,
                "count": 2
            },
            {
                "length": 250,
                "count": 5
            },
            {
                "length": 557,
                "count": 2
            }
        ],
        "G21": [
            {
                "length": 876,
                "count": 5
            },
            {
                "length": 452,
                "count": 1
            }
        ]
    },
    "PROFIELKLEUR: Brons": {
        "G45": [
            {
              "length": 1222,
              "count": 5
            },
            {
              "length": 887,
              "count": 2
            }
        ],
        "G56": [
            {
                "length": 123,
                "count": 1
            }
        ]
    }
}
```

#### Opbouw van dit object

De mapping van de data heb ik hieronder beschreven. In het `ProductieStaat.json` staan ongeveer 75 productiestaten.  

```json
{
    "<saw.profielkleur.title>": {
        "<saw.*.title>": {
            "length": "<saw.*.value>",
            "count": "<saw.*.amount>"
        }
    }...
}
```

Voor deze casus hoef jij je alleen maar te focussen op het `saw` object. In dit object kan je alle profielen vinden die 
gezaagd moeten worden die dag. De uitdaging is dat de profielen niet in de profielnamen ("G40") staan, maar als 
bijvoorbeeld `liggerG40`. Van `liggerg40` moet je `G40` maken. 

De profielkleur kan je vinden in `profielkleur.title`. Deze titel kan je gebruiken als unique identifier.

## Scope van deze casus

De scope van deze casus is alleen het muteren van het `ProductieStaat.json` bestand naar het dit object.
Heb je een soortgelijk object kunnen maken dan voldoe je aan de scope van deze casus. 

In elke productiestaat kan je alle data vinden in het `saw` object.

## Requirements

Om de dataset juist te muteren moet je aan een paar requirements voldoen om tot het juiste antwoord te komen:
- Je hoeft alleen de profielen mee te nemen waar een G nummer in voorkomt, dus bijvoorbeeld:
  - `OnderbovenProfielg41` is een profiel
  - `exactinputtaatsdeur_z` is geen profiel. Hier zit namelijk geen G nummer in verwerkt
- Er zijn ook twee profielen die in elkaar passen. En dus dezelfde kleur en maat hebben. Voor dit soort 
profielcombinaties heb je bijvoorbeeld `staanderg54g56taatsdeur`. Zoals je ziet zitten hier twee G nummer in. Dus als 
je in dit object een `value` heb van `2396` en een `amount` van `2`. Dan krijg je het volgende resultaat (profielkleur 
is `PROFIELKLEUR: RAL 7032 Kiezel grijs`):
```json
{
    "PROFIELKLEUR: RAL 7032 Kiezel grijs": {
        "G54": {
            "length": 2396,
            "count": 2
        },
        "G56": {
            "length": 2396,
            "count": 2
        }
    }
}
```
- Dezelfde G nummers en kleuren moeten bij elkaar gemapped worden. Ik zal hier een voorbeeld geven van de huidige 
situatie en de gewenste situatie:
#### Huidige situatie
```json
{
    "id": 123,
    "saw": {
        "liggerg40": {
            "title": "Ligger G40",
            "amount": 2,
            "value": 1600
        },
        "staanderg54g56taatsdeur": {
            "title": "Staander G54 + G56 taatsdeur",
            "amount": 2,
            "value": 2396
        },
        "profielkleur": {
            "title": "PROFIELKLEUR: RAL 7032 Kiezel grijs"
        }
    }
},
{
    "id": 456,
    "saw": {
        "staandersg70verstekdeur": {
            "title": "Staanders G70 verstek deur",
            "amount": 2,
            "value": 2400
        },
        "staanderg62g69deur": {
            "title": "Staander G69 deur",
            "amount": 1,
            "value": 2400
        },
        "profielkleur": {
            "title": "PROFIELKLEUR: Leem"
        }
    }
},
{
    "id": 789,
    "saw": {
        "staandersg70verstekdeur": {
            "title": "Staanders G70 verstek deur",
            "amount": 2,
            "value": 2785
        },
        "staanderg62g69deur": {
            "title": "Staander G69 deur",
            "amount": 1,
            "value": 2785
        },
        "liggerg40": {
            "title": "Ligger G40",
            "amount": 2,
            "value": 1600
        },
        "profielkleur": {
            "title": "PROFIELKLEUR: Leem"
        }
    }
}
```

#### Gewenste situatie

```json
{
    "PROFIELKLEUR: RAL 7032 Kiezel grijs": {
        "G40": [
            {
                "length": 1600,
                "count": 2
            }
        ],
        "G54": [
            {
                "length": 2396,
                "count": 2
            }
        ],
        "G56": [
            {
                "length": 2396,
                "count": 2
            }
        ]
    },
    "PROFIELKLEUR: Leem": {
        "G70": [
            {
              "length": 2785,
              "count": 2
            }
        ],
        "G62": [
            {
                "length": 2785,
                "count": 1
            }
        ],
        "G69": [
            {
                "length": 2785,
                "count": 1
            }
        ],
        "G40": [
            {
                "length": 1600,
                "count": 1
            }
        ]
    }
}
```

### Verdere informatie
- Als je een vraag wil stellen kan je naar ons 
[Q&A bord](https://github.com/gewoongers/zaaglijst-casus/discussions/categories/q-a).
- Als je leuke ideetjes hebt voor deze casus kan je idee posten op ons 
[ideeën bord](https://github.com/gewoongers/zaaglijst-casus/discussions/categories/ideas)
- Als je een bug heb gevonden zou het top zijn als je het ons zou kunnen laten weten! Dit kan je
[hier](https://github.com/gewoongers/zaaglijst-casus/issues/new) laten weten.
