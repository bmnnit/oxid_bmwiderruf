# bmWiderruf — Widerrufs-Formular nach §312k BGB

OXID-Modul, das einen rechtskonformen "Widerrufsbutton" inkl. Bestätigungsseite, Persistierung der Erklärung und automatischer Eingangsbestätigung per E-Mail bereitstellt.

Pflicht ab **19.06.2026** für alle B2C-Online-Händler in Deutschland.

> **Welche Variante brauche ich?**
> - **OXID 7.3 / Twig** → Branch [`twig`](https://github.com/bmnnit/oxid_bmwiderruf/tree/twig) (diese Datei)
> - **OXID 6.5 / Smarty** → Branch [`main`](https://github.com/bmnnit/oxid_bmwiderruf)
>
> Die beiden Branches sind unabhängige Implementierungen (Smarty- vs. Twig-Templates, leicht unterschiedliche OXID-API), nicht ein Fork-and-merge-Workflow. Wähle den Branch, der zu deiner OXID-Version passt.

> Minimal solution. OXID wird voraussichtlich eine eigene Lösung nachliefern — bis dahin schliesst dieses Modul die Lücke.
> Achtung: CE-Edition berücksichtigt die `OXSHOPID`-Spalte nicht (Single-Shop-Annahme).
> Mit Lizenz, aber du schuldest mir ein Bier 🍺

---

## Funktionsumfang

- Frontend-Seite `/widerruf` (auch erreichbar über `?cl=widerruf`)
- Formular mit den Pflichtfeldern **Name**, **Bestellnummer**, **E-Mail** und einem optionalen freien Textfeld **Grund**
- Submit-Button **"Widerruf bestätigen"** mit gesetzlich vorgeschriebenem Footnote-Text
- Strikte Verifizierung: Bestellnummer + E-Mail müssen einem realen Eintrag in `oxorder` (`OXORDERNR` + `OXBILLEMAIL`) entsprechen
- Konfigurierbares max. Bestellalter (Modul-Setting, Default 30 Tage; `0` = unbegrenzt)
- Persistierung der Erklärung in der neuen Tabelle **`bmwiderruf`** (Audit-Trail mit `OXCREATED`-Timestamp und Submitter-IP)
- Zwei automatische E-Mails: Eingangsbestätigung an Kunde + Benachrichtigung an die Shop-Bestelladresse (`oxshops.oxorderemail`)
- Betreff beider Mails: **"Widerruf Kaufvertrag"** (Sprachfile-Eintrag, anpassbar)
- Mail-Bodies werden über **CMS-Snippets** in `oxcontents` (LoadIDs `bmwiderruf_customer` / `bmwiderruf_admin`) geladen — Inhalt jederzeit über OXID-Admin editierbar
- Admin-Übersicht **"Widerrufe"** unter *Bestellungen verwalten* listet die letzten 500 Anfragen
- Statische SEO-URL `/widerruf/` wird beim Aktivieren automatisch in `oxseo` eingetragen

---

## Architektur

| Datei | Aufgabe |
|---|---|
| `metadata.php` | Modul-Definition v2.1, Controller- + Template-Registry, Modul-Setting `iBmWiderrufMaxAgeDays` |
| `menu.xml` | Hängt die Admin-Liste unter `MAINMENU id="mxorders"` |
| `Core/Events.php` | `onActivate`: legt `bmwiderruf`-Tabelle an, fügt `OXNAME`-Spalte idempotent hinzu, seedet CMS-Snippets (INSERT IGNORE) und SEO-URL |
| `Core/Email.php` | Erweitert `OxidEsales\Eshop\Core\Email`, sendet je eine HTML+Plain-Mail an Kunde und Admin |
| `Application/Controller/WiderrufController.php` | Frontend `?cl=widerruf` mit `send()`-Handler: liest `name`, `ordernr`, `email`, `message`; validiert; persistiert; löst Mailversand aus |
| `Application/Controller/Admin/WiderrufList.php` | Liest die letzten 500 Einträge aus `bmwiderruf` JOIN `oxorder` |
| `Application/Model/Widerruf.php` | OXID-BaseModel für `bmwiderruf` |
| `views/tpl/form/widerruf.tpl` | Bootstrap-Formular |
| `views/tpl/page/info/widerruf.tpl` | Page-Wrapper |
| `views/tpl/page/info/widerruf_thanks.tpl` | Danke-Seite nach erfolgreicher Übermittlung |
| `views/tpl/email/html\|plain/widerruf_{customer,admin}.tpl` | Mail-Templates (binden CMS-Snippet ein) |
| `views/admin/tpl/bm_widerruf_list.tpl` | Admin-Liste |
| `Application/translations/{de,en}/bmwiderruf_lang.php` | Frontend + Mail-Strings |
| `Application/views/admin/{de,en}/bmwiderruf_lang.php` | Admin-Labels + Menü-Eintrag + Modul-Settings |
| `sql/install.sql` | Manuell ausführbares DDL (gleiche Definition wie in `Events::onActivate`) |

### Datenmodell `bmwiderruf`

| Spalte | Typ | Beschreibung |
|---|---|---|
| `OXID` | CHAR(32) | UUID |
| `OXSHOPID` | INT | für Multishop (siehe Hinweis CE-Edition oben) |
| `OXORDERID` | CHAR(32) | FK auf `oxorder.OXID` |
| `OXORDERNR` | INT | gespiegelt aus `oxorder.OXORDERNR` |
| `OXNAME` | VARCHAR(255) | vom Kunden eingegebener Name |
| `OXEMAIL` | VARCHAR(255) | vom Kunden eingegebene E-Mail (= `oxorder.OXBILLEMAIL`) |
| `OXMESSAGE` | TEXT | optionaler Grund (Freitext) |
| `OXIP` | VARCHAR(45) | `REMOTE_ADDR` zum Submit-Zeitpunkt |
| `OXCREATED` | TIMESTAMP | Eingangsdatum, DB-Default `CURRENT_TIMESTAMP` |

---

## Installation

### Voraussetzungen

- OXID eShop 6.5.x mit PHP ≥ 8.1
- Composer-Autoload-Eintrag in der Shop-Root-`composer.json`:
  ```json
  "Bmnnit\\bmWiderruf\\": "./source/modules/bmnnit/bmWiderruf",
  ```

### Schritte

```bash
# 1. Repository auf den Server bringen (z.B. via Git-Pull)
# 2. Autoload neu generieren
composer dump-autoload

# 3. Modul-Konfiguration in OXID einspielen (Pflicht in OXID 6.5)
vendor/bin/oe-console oe:module:install-configuration source/modules/bmnnit/bmWiderruf
vendor/bin/oe-console oe:module:apply-configuration

# 4. Modul im OXID-Admin aktivieren
#    Erweiterungen → Module → [BM] Bmnnit Widerruf-Formular → aktivieren
#    -> Events::onActivate legt Tabelle + CMS-Snippets + SEO-URL an
```

> **Wichtig nach jedem Pull, der `metadata.php` verändert** (z.B. neue
> Settings, neue Controller, neue Templates):
> `oe:module:install-configuration` + `oe:module:apply-configuration` erneut
> ausführen — ein reines Deaktivieren/Aktivieren reicht in OXID 6.5 nicht.

### Updates der CMS-Mail-Bodies

`INSERT IGNORE` lässt vom Admin editierte Snippets in Ruhe. Wenn nach einem
Modul-Update die neuen Default-Texte gewünscht sind, manuell:

1. OXID-Admin → *Kundeninformationen → CMS-Seiten*
2. Einträge `bmwiderruf_customer` und `bmwiderruf_admin` löschen
3. Modul deaktivieren + aktivieren → frische Defaults werden geseedet

---

## Konfiguration

### Modul-Einstellungen

OXID-Admin → *Erweiterungen → Module → bmWiderruf → Einstellungen*

| Setting | Default | Beschreibung |
|---|---|---|
| `iBmWiderrufMaxAgeDays` | `30` | Max. Alter der Bestellung in Tagen. Bei Überschreitung wird die Anfrage zurückgewiesen (`BMNNIT_WIDERRUF_ERR_TOO_OLD`). `0` deaktiviert die Prüfung. |

### Mail-Empfänger

- **Kunde**: an die im Formular angegebene E-Mail
- **Shop / Admin**: an `oxshops.oxorderemail` (also die in OXID hinterlegte Bestell-Adresse)

### Mail-Inhalt anpassen

OXID-Admin → *Kundeninformationen → CMS-Seiten* → folgende Snippets editieren:

- `bmwiderruf_customer` — Bestätigungsmail an den Kunden
- `bmwiderruf_admin` — Benachrichtigung an den Shop

Verfügbare Smarty-Variablen (alle mit `|escape` rendern!):

```smarty
[{$submission->bmwiderruf__oxname->value|escape}]
[{$submission->bmwiderruf__oxordernr->value|escape}]
[{$submission->bmwiderruf__oxemail->value|escape}]
[{$submission->bmwiderruf__oxmessage->value|escape}]
[{$submission->bmwiderruf__oxip->value|escape}]
[{$submission->bmwiderruf__oxcreated->value|escape}]
[{$order.OXBILLFNAME|escape}]   [{$order.OXBILLLNAME|escape}]
[{$order.OXORDERDATE|escape}]
[{$shop->oxshops__oxname->value|escape}]
```

### Mail-Betreff anpassen

Im Sprachfile `Application/translations/{de,en}/bmwiderruf_lang.php`:

```php
'BMNNIT_WIDERRUF_EMAIL_SUBJECT_CUSTOMER' => 'Widerruf Kaufvertrag',
'BMNNIT_WIDERRUF_EMAIL_SUBJECT_ADMIN'    => 'Widerruf Kaufvertrag',
```

---

## Admin-Übersicht

OXID-Admin → *Bestellungen verwalten → Widerrufe*

Zeigt die letzten 500 eingegangenen Widerrufs-Anfragen sortiert nach
Eingangsdatum mit folgenden Spalten:

| Eingangsdatum | Bestellnr. | E-Mail | Kunde | Grund | IP |

Alle Felder werden mit Smarty-`|escape` ausgegeben (XSS-fest auch bei
manipulierten DB-Werten).

---

## Sicherheit

- **XSS**: Alle benutzerkontrollierten Werte werden mit `|escape` ausgegeben (Formular-Re-Render, Admin-Liste, Mail-Bodies, CMS-Snippets).
- **Persistierung**: `getRequestParameter()` (Rohwert) wird im Controller verwendet, damit die DB den unveränderten Original-Input enthält; Escape geschieht ausschließlich beim Output.
- **CSRF**: aktuell **kein** dezidierter Token; OXID-Cookie-Session bietet Basisschutz. Bei Bedarf `stoken`-Check ergänzen.
- **Spam**: Kein Captcha. Schutz erfolgt indirekt durch strikte Validierung (Bestellnr. + E-Mail müssen einem realen Order-Datensatz entsprechen, plus Alters-Limit).

---

## §312k-BGB-Compliance — Mapping

| Anforderung | Umsetzung im Modul |
|---|---|
| Widerruf-Button "hervorgehoben platziert" | Footer-Link **manuell im Theme** ergänzen (z.B. `<a href="?cl=widerruf">Kaufvertrag widerrufen</a>`) — noch nicht enthalten |
| Bestätigungsseite mit Formular | `views/tpl/page/info/widerruf.tpl` |
| Button **"Widerruf bestätigen"** | Sprachstring `BMNNIT_WIDERRUF_SUBMIT` |
| Footnote-Text *"Mit dem Klick auf den Button widerrufe ich meinen Kaufvertrag."* | Sprachstring `BMNNIT_WIDERRUF_BUTTON_DISCLAIMER` |
| Pflichtfelder Name + Vertrags-ID + Kommunikationsmittel | Name + Bestellnummer + E-Mail |
| Automatische Bestätigungsmail "unverzüglich" | Versand direkt im `send()`-Handler nach erfolgreichem `save()` |
| Mailinhalt: Erklärung + Datum + Uhrzeit des Eingangs | CMS-Snippet rendert `OXCREATED` als "Eingangsdatum" |
| Neutrale Empfangsbestätigung (keine vorgreifende Wirksamkeits-Bestätigung) | Formulierung *"wir bestätigen den Eingang Ihrer Widerrufserklärung"* |
| Optionaler Grund (NICHT pflicht) | Freitext-Feld `message`, optional |

---

## Bekannte Punkte

- **Footer-Link** im Theme ist nicht Teil des Moduls — muss im Theme (`source/Application/views/<dein_theme>/tpl/…`) eingebaut werden.
- **SEO-URL `/widerruf/`** wird beim Aktivieren in `oxseo` eingetragen. Falls die saubere URL **nicht** funktioniert, ist `?cl=widerruf` der zuverlässige Fallback.
- **CSRF-Token** wird derzeit nicht geprüft.
- **OXSHOPID** in `bmwiderruf` wird zwar gespeichert, im CE-Edition-Setup aber faktisch ignoriert (alle Einträge landen unter Shop 1).

---

## Lizenz

MIT License

Copyright (c) 2026 Baumann IT-Dienstleistungen

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Kontakt: <info@bmnnit.com>
