#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <curl/curl.h>

static size_t write_cb(void *ptr, size_t size, size_t nmemb, void *userdata) {
    size_t realsize = size * nmemb;
    char **buffer = (char **)userdata;
    char *newbuf;

    newbuf = realloc(*buffer, (*buffer ? strlen(*buffer) : 0) + realsize + 1);
    if (!newbuf) return 0;

    if (!*buffer) newbuf[0] = '\0';
    strncat(newbuf, ptr, realsize);

    *buffer = newbuf;
    return realsize;
}

char *simple_qrz_get(const char *call) {
    CURL *ch;
    CURLcode res;
    char *out;
    char agent[256];
    char url[256];

    /* --- variabili tutte dichiarate qui sopra --- */

    out = NULL;
    snprintf(agent, sizeof(agent),
             "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
             "AppleWebKit/537.36 (KHTML, like Gecko) "
             "Chrome/100.0.4896.75 Safari/537.36");

    snprintf(url, sizeof(url), "https://www.qrz.com/lookup/%s", call);

    ch = curl_easy_init();
    if (!ch) return NULL;

    curl_easy_setopt(ch, CURLOPT_URL, url);
    curl_easy_setopt(ch, CURLOPT_RETURNTRANSFER, 1L);
    curl_easy_setopt(ch, CURLOPT_SSL_VERIFYPEER, 0L);
    curl_easy_setopt(ch, CURLOPT_FOLLOWLOCATION, 1L);
    curl_easy_setopt(ch, CURLOPT_WRITEFUNCTION, write_cb);
    curl_easy_setopt(ch, CURLOPT_WRITEDATA, &out);
    curl_easy_setopt(ch, CURLOPT_USERAGENT, agent);

    res = curl_easy_perform(ch);
    curl_easy_cleanup(ch);

    if (res != CURLE_OK) {
        free(out);
        return NULL;
    }
    return out; /* l’utente deve fare free(out) */
}
