# DOI Content Negotiation Scripts

Some experiments with CrossRef's [DOI Content Negotiation API](http://crosscite.org/cn/) to generate citation data. The code is kind of ragged, but it works. 

The basic idea here is to send a DOI URI GET to CrossRef using CURL. Depending on the content type sent in the GET request, the DOI resolver at dx.doi.org can be instructed to send bibliographic data by redirecting the request to a metadata service hosted by the DOI's registration agency, CrossRef and DataCite.