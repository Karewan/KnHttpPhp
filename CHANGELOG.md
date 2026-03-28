v1.1.7 (2026-03-28)
----------------------------

* HTTP 2 for TLS only, HTTP 1.1 for clear text

v1.1.6 (2026-03-28)
----------------------------
* Improved performances of execMulti
* Various fixes and improvements

v1.1.5 (2026-02-26)
----------------------------
* Added HEAD and OPTION methods
* Added execForHeaders and setForHeaders methods
* Updated getPreparedUrl to be public

v1.1.4 (2026-02-26)
----------------------------
* Added isSuccessful method in KnResponse
* Added getCurlInfo method in KnResponse
* Added getTotalTime method in KnResponse
* Added getErrorLabel method in KnResponse
* Added getFullErrorTrace method in KnResponse
* Added getters methods in KnRequest

v1.1.3 (2026-02-16)
----------------------------
* Added PATCH method
* Added PHP 8.5 compatibility
* Various fixes and improvements

v1.1.2 (2025-10-19)
----------------------------
* Fixed response parsing when using curl_multi_getcontent

v1.1.1 (2025-10-19)
----------------------------
* Added a concurrency and maxConnPerHost params to execMulti

v1.1.0 (2025-10-19)
----------------------------
* Added async parallel requests execution
* Fixed the "clearHeaders" method
* The "clearBodies" method now return the class instance
* Various fixes and improvements

v1.0.2 (2025-06-26)
----------------------------
* Removed Content-Type header overwrite for string and json body

v1.0.1 (2025-03-26)
----------------------------
* Added automatic "Accept-Encoding" header by curl with appropriate algorithms

v1.0.0 (2025-03-23)
----------------------------
* Initial release
