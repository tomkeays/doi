# DOI Content Negotiation Scripts

Some experiments with CrossRef's [DOI Content Negotiation API](http://crosscite.org/cn/) to generate citation data. The code is kind of ragged, but it works. 

The basic idea here is to send a DOI URI GET to CrossRef using CURL. Depending on the content type sent in the GET request, the DOI resolver at dx.doi.org can be instructed to send bibliographic data by redirecting the request to a metadata service hosted by the DOI's registration agency, CrossRef and DataCite.

## doi_citation.php

This script pulls bibliographic data from the DOI resolver formatted as JSON, which is then stored in a PHP array, and then formatted in a couple of different citation styles, which I puzzled out myself. At this point, I hadn't figured out that I could use CSL and save myself work. 

## doi_coins.php

Basically a riff on the doi_citation script, but outputs [COinS](http://ocoins.info) rather than formatted citations. Like all of these scripts so far, only good for generating OpenURL context objects for journal articles. 
## doi_csl.php

After I wrote the doi_citation script, I learned that you could query the DOI resolver with a different content type request that it output a citation style following [Citation Style Language style rules](https://github.com/citation-style-language/styles). The output is plain text (so may be missing required bolding and emphasis for some styles), but it saves a lot of time and scales better than writing the rules yourself. 