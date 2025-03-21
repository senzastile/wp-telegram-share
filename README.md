# WP Telegram Share

**WP Telegram Share** è un plugin WordPress che permette di condividere automaticamente i tuoi post su un canale Telegram.

## 🚀 Caratteristiche

- **Condivisione automatica**: Invia automaticamente nuovi post al tuo canale Telegram quando vengono pubblicati
- **Messaggi personalizzabili**: Template configurabile con segnaposto per titolo, estratto, link, autore, data
- **Supporto HTML**: Formattazione avanzata nei messaggi Telegram
- **Immagini in evidenza**: Opzione per includere automaticamente l'immagine in evidenza del post
- **Controllo granulare**: Possibilità di disabilitare la condivisione per post specifici
- **Condivisione manuale**: Condividi manualmente post già pubblicati
- **Test di connessione**: Verifica facilmente la tua configurazione

## 📋 Requisiti

- WordPress 5.0+
- PHP 7.0+
- Un bot Telegram attivo
- Un canale Telegram dove il bot ha i permessi di amministratore

## 🔧 Installazione

### Metodo standard
1. Vai su Plugin > Aggiungi nuovo nella tua dashboard WordPress
2. Cerca "WP Telegram Share"
3. Clicca "Installa ora" e poi "Attiva"

### Installazione manuale
1. Scarica il file zip dalla [pagina delle release](https://github.com/senzastile/wp-telegram-share/releases)
2. Vai su Plugin > Aggiungi nuovo > Carica plugin nella tua dashboard WordPress
3. Carica il file zip e clicca "Installa ora"
4. Attiva il plugin

## ⚙️ Configurazione

1. Crea un bot Telegram tramite [@BotFather](https://t.me/BotFather) e ottieni il token
2. Aggiungi il bot come amministratore al tuo canale Telegram
3. Vai su Impostazioni > WP Telegram Share nella tua dashboard WordPress
4. Inserisci il token del bot e l'ID del canale (es. @nomedelcanale)
5. Personalizza il formato del messaggio e le altre opzioni
6. Clicca su "Testa Connessione" per verificare che tutto funzioni correttamente

### Formato messaggio

Puoi personalizzare il messaggio utilizzando i seguenti segnaposto:
- `{title}` - Titolo del post
- `{excerpt}` - Estratto del post
- `{permalink}` - Link permanente al post
- `{author}` - Nome dell'autore
- `{date}` - Data di pubblicazione

Esempio:
```
📢 Nuovo articolo: {title}

{excerpt}

🔗 {permalink}
✍️ {author}
📅 {date}
```

## ❓ FAQ

### Come creo un bot Telegram?
1. Apri Telegram e cerca @BotFather
2. Invia il comando `/newbot` e segui le istruzioni
3. Alla fine riceverai un token API da inserire nelle impostazioni del plugin

### Come ottengo l'ID del mio canale Telegram?
Se il tuo canale ha un nome utente pubblico (es. @miocanale), puoi semplicemente inserire quel nome incluso il simbolo @.

Per canali privati:
1. Invia un messaggio al canale
2. Visita `https://api.telegram.org/bot[TOKEN]/getUpdates` (sostituisci [TOKEN] con il token del tuo bot)
3. Cerca "chat":{"id": e copia il numero (solitamente inizia con -100)

### Posso disabilitare la condivisione per alcuni post?
Sì, nella pagina di modifica di ogni post troverai un metabox "Condivisione Telegram" dove puoi disabilitare la condivisione automatica.

## 🔄 Changelog

### 1.0.0
* Versione iniziale

## 🛠️ Sviluppo

Questo plugin è open source e accogliamo contributi dalla community. Ecco come puoi contribuire:

### Configurazione locale
```bash
# Clona il repository
git clone https://github.com/senzastile/wp-telegram-share.git

# Entra nella directory
cd wp-telegram-share
```

### Reporting dei bug
Hai trovato un bug? Apri una [issue](https://github.com/senzastile/wp-telegram-share/issues) con:

1. Descrizione del problema
2. Passaggi per riprodurlo
3. Versione di WordPress e PHP
4. Eventuali errori nel log

## 📜 Licenza

Questo plugin è rilasciato sotto la licenza [GPL v2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html) o successiva.

## 👥 Autori

Sviluppato da [Senza Stile](https://www.senzastile.it/)

## 🔗 Link utili

- [Sito web](https://www.senzastile.it/wp-telegram-share)
- [Documentazione API Telegram](https://core.telegram.org/bots/api)

