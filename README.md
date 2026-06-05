<<<<<<< HEAD
# Currency Price API

Lightweight PHP API for extracting real‑time currency prices from tgju.org.

This API scrapes currency market data and returns it as a clean JSON response.
It is designed to be simple, fast, and easy to integrate into other systems.

--------------------------------------------------

Author

Mehdi Allahverdi
Developer & Software Engineer

Email: mdiall.offical@gmail.com

Developed by Lernida

Website: https://lernida.ir
Telegram: https://t.me/lernida

--------------------------------------------------

Features

- Simple REST API
- Lightweight PHP implementation
- JSON formatted responses
- Fast scraping system
- Easy integration with any backend or frontend
- Multiple currency support

--------------------------------------------------

Installation

1. Upload the project to your server
2. Make sure PHP is enabled on your server
3. Access the API endpoint from your browser or application

Example:

https://yourdomain.com/api.php?n=price_dollar_rl

--------------------------------------------------

API Usage

Request

GET /api.php?n={currency_slug}

Example

/api.php?n=price_eur

or multiple currencies

/api.php?n=price_dollar_rl,price_eur,price_aed

--------------------------------------------------

Example Response

{
  "success": true,
  "message": "OK",
  "data": {
    "price_dollar_rl": {
      "name": "US Dollar",
      "price": "650000",
      "change": "+0.5%",
      "min": "640000",
      "max": "655000",
      "date": "2026-06-04"
    }
  }
}

--------------------------------------------------

Supported Currency Slugs

The API only accepts the following currency identifiers:

price_dollar_rl
price_eur
price_aed
price_gbp
price_try
price_chf
price_cny
price_jpy
price_krw
price_cad
price_aud
price_nzd
price_sgd
price_inr
price_pkr
price_iqd
price_syp
price_afn
price_dkk
price_sek
price_nok
price_sar
price_qar
price_omr
price_kwd
price_bhd
price_myr
price_thb
price_hkd
price_rub
price_azn
price_amd
price_gel
price_kgs
price_tjs
price_tmt

Requests using other values will return "Not Found".

--------------------------------------------------

Project Structure

currency-api
│
├── index.php
├── README.md
└── storage
    ├── cache.json
    └── rate_limit.json

--------------------------------------------------

License

MIT License

You are free to use, modify, and distribute this software with attribution.

--------------------------------------------------

Notes

This API works by scraping public data from tgju.org.
If the website structure changes, the scraper may require updates.

--------------------------------------------------

Contributing

Pull requests and improvements are welcome.
=======
# CurrencyScraper
A lightweight open‑source PHP project that extracts and provides real-time exchange rates for world currencies from online sources. Designed for easy integration into e‑commerce platforms, financial tools, and any application that requires up‑to‑date currency data.
>>>>>>> f81d624742da991e78904b6d8dd8a2d315094bab
