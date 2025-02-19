# Moodle KAT-HS Adaptive Quiz Plugin
<!-- ## Badges -->
![Static Badge](https://img.shields.io/badge/Moodle-4.x-green?style=flat-square)
![Static Badge](https://img.shields.io/badge/Moodle->3.8-green?style=flat-square)
## Description
This Plugin provides an adaptive quiz. The calculation of the CAT logic is done on a spearate Server and can there be updated via a R-Script without touching the Moodle Plugin. The communication between this Plugin and the R-Server is done via a [REST-API](#rest-api). 






<!-- ## Visuals
Depending on what you are making, it can be a good idea to include screenshots or even a video (you'll frequently see GIFs rather than actual videos). Tools like ttygif can help, but check out Asciinema for a more sophisticated method. -->

## Setup/Installation
This Plugin can be installed via zip upload on your Moodle installation.

## Usage
### Test Settings

The current version of the Moodle plugin has the following options for the test settings. 

--------------------------------------------------------------------
| Setting     | Values | Description | 
| :------------- |:-------------| :------------- |
| Name |String| Name of test |
| Description|String| Description/instructions of test |
| Attempts allowed  |Integer| Maximum number of attempts per test taker |
| Required password  |String| Password to start the test |
| Browser security  |Yes/No| If yes: Should the test start in a kiosk mode? The test person's PC must have a JavaScript-enabled web browser. |
| Question pool  |Dropdown menu| Selection of the question pool to be used |
| Attempt feedback |String| Feedback displayed at the end of the test |
| Show ability measure to students |Yes/No| Should the ability estimate including standard error be displayed to the students after completion of the test? |
| Show quiz progress to students  |Yes/No| If Yes, the test subjects are shown a progress bar for the test. |
| Task selection non adaptiv part  |random/sequential| This setting refers to tasks that are not selected adaptively. These tasks can be selected either randomly or sequentially, whereby the sequential selection implements the order in which the tasks are specified in the item pool.|
| Number of calibration clusters |Integer| Number of item clusters with new items that serve to increase the item pool. Note: The sum of the three cluster types also determines the number of test booklets that are automatically compiled. Balanced Latin Square designs (z. B. [Williams, 1949](https://adsabs.harvard.edu/full/1949ausra...2..149w); [Frey, Hartig & Rupp, 2009](https://onlinelibrary.wiley.com/doi/10.1111/j.1745-3992.2009.00154.x)) are used to balance cluster positions and first-order carry-over effects. The sum of the three cluster types must correspond to an integer proportion of the test length. The maximum number of clusters is equal to the test length.|
| Number of linking clusters |Integer| Number of item clusters, with items that already have parameter estimation and are used to link consecutive test cycles. Note: The sum of the three cluster types also determines the number of test booklets that are automatically compiled. Balanced Latin Square designs (z. B. [Williams, 1949](https://adsabs.harvard.edu/full/1949ausra...2..149w); [Frey, Hartig & Rupp, 2009](https://onlinelibrary.wiley.com/doi/10.1111/j.1745-3992.2009.00154.x)) are used to balance cluster positions and first-order carry-over effects. The sum of the three cluster types must correspond to an integer proportion of the test length. The maximum number of clusters is equal to the test length.|
| Number of adaptive clusters |Integer| Number of item clusters whose items are to be selected adaptively. Note: The sum of the three cluster types also determines the number of test booklets that are automatically compiled. Balanced Latin Square designs (z. B. [Williams, 1949](https://adsabs.harvard.edu/full/1949ausra...2..149w); [Frey, Hartig & Rupp, 2009](https://onlinelibrary.wiley.com/doi/10.1111/j.1745-3992.2009.00154.x)) are used to balance cluster positions and first-order carry-over effects. The sum of the three cluster types must correspond to an integer proportion of the test length. The maximum number of clusters is equal to the test length.|
| Person parameter estimation  |EAP/MAP/WLE/MLE| The person parameter estimator used during the test. The following estimators are available: Expected-A-Posteriori (EAP; [Bock & Mislevy, 1982](https://doi.org/10.1177/014662168200600405)), Maximum-A-Posteriori (MAP; [Mislevy, 1986](https://doi.org/10.1007/BF02293979)), Weighted Likelihood Estimate (WLE; [Warm, 1989](https://doi.org/10.1007/BF02294627)) and Maximum Likelihood Estimate (MLE; [Lord, 1980](https://www.taylorfrancis.com/books/mono/10.4324/9780203056615/applications-item-response-theory-practical-testing-problems-lord)). |
| Task selection adaptive part  |Maximum Information (Default), Maximum Expected Posterior Variance, Maximum Expected Information, Integration based Kullback-Leibler|  The criterion for adaptive item selection in adaptive clusters. Available for selection: Maximum Information (Default), Maximum Expected Posterior Variance, Maximum Expected Information, Integration based Kullback-Leibler |
| Randomesque Exposure Control  |Checkbox| Should randomesque exposure control method be used? |
| Best matching tasks   |Integer| Only if Randomesque Exposure Control has been selected: Specifies the number of best matching items to randomly select from|
| User-defined specification of proportions of individual content areas in the overall test?    | Checkbox | If selected, proportions of certain content areas of the overall test can be specified (see distribution of content areas) |
| Content area distribution    |String| Semicolon-separated list that specifies the content area and the proportion (in the value range from 0 to 1) of the respective content area in the overall test. E.g. catname1:0.2;catname2:0.3;catname3:0.5 |
| Minimum number of questions     |Integer| Minimum number of items |
| Maximum number of questions     | Integer | Maximum number of items |
| Standard Error to stop     |Float| If the standard error of the individual ability estimate falls below this value, the test stops automatically. Not taken into account if set to 0 (default). |

#### pers_est
Internally, the property ```pers_est``` has following short values:

-------------------------------------------------------------------- 
| Short	  | Description | 
| ------------- | ------------- | 
| MAP  | Maximum-A-Posteriori	|
| EAP  | Expected-A-Posteriori	|
| WLE  | Weighted Likelihood Estimation	|
| ML  | Maximum Likelihood Estimation	|


#### criteria_adaptive
Internally, the property ```criteria_adaptive``` has following short values:  

-------------------------------------------------------------------- 
| Short	  | Description | 
| ------------- | ------------- | 
| MI  | Maximum Information	|
| MEPV  | Minimum Expected Posterior Variance	|
| MEI  | Maximum Expected Information	|
| IKL  | Integration-based Kullback-Leibler  	|




### Item Pool
The items in the item pool need to have certain tags (specified in the question pool in Moodle) to be recognized by the plugin. Only the adpq_1 tag is mandatory. Other tags are optional. The tags are:

--------------------------------------------------------------------
| Tag      | Example | Description | 
| ------------- |-------------| ------------- |
| adpq_1 |  adpq_1 |  Marker that item is marked for adaptive item pool | 
| enemy_[array with ItemID] | enemy_[ItemID1;ItemID456] | ItemID of enemy items, i.e. items that should not appear together with the respective item in a test. Multiple values separated by a semicolon. *(Omit tag if there are no enemy items.)*|
| ca_[array with strings] | ca_[comprehension;knowledge;fairy tale;MC] | This tag can be used to specify different content areas covered by the item. It can also be used for other characteristics relevant for item selection (e.g. item type, cognitive requirement level). Multiple values separated by a semicolon. The proportions of the various content areas specified here in the overall test can be specified in the test settings.*(Omit tag if not necessary.)* |
| diff_[array with floats] | diff_[1.2] for dichotomous items<br><br> diff_[1.2;2.34;5.54] for polytomous items | Difficulty parameter. Can have several floating point values. One value for dichotomous items or several values for polytomous items, separated by semicolons. |
| disc_[array with numbers] | disc_[1.4] for one dimensional tests<br><br>disc_[1.9;0;1.4] for three dimensional tests| Discrimination parameter. Can have several floating point values. One value for one-dimensional tests or multiple values (specifies charge pattern) for multidimensional tests, separated by semicolon. |				
| cluster_CLUSTERNAME | cluster_A, cluster_C1, cluster_L2  | Cluster in the sense of the continuous calibration strategy (e.g. [Fink et al., 2018](https://www.psychologie-aktuell.com/fileadmin/Redaktion/Journale/ptam_3-2018_327-346.pdf)) to which the item belongs. A distinction is made between adaptive clusters (cluster_A), calibration clusters (cluster_C#) and linking clusters (cluster_L#). See table below for how to specify different test designs using this cluster structure.
|					

#### Continuous Calibration Strategy (CCS) Cluster Specification

--------------------------------------------------------------------
| Type of test    | Number of adaptive cluster | Number of calibration cluster | Number of linking cluster | 
| ------------- |:-------------:| :-------------: | :-------------: |
| Single/first adiministration non-adaptive test |= 0| ≥ 1| = 0 |
|Reccurent administration of non-adaptive test without increasing the item pool |= 0| = 0| ≥ 1 |
|Linking + increasing the item pool |= 0| ≥ 1| ≥ 1 |
|CCS without increasing the item pool |≥ 1| = 0| ≥ 1 |
|CCS  |≥ 1| ≥ 1| ≥ 1 |
|Fully adaptive test  |= 1| = 0| = 0 |
## Extension
### Test Settings

--------------------------------------------------------------------
| Files         | Description  | 
| ------------- | ------------- |
| `/db/install.xml` | Add SQL statements for the database table that settings are saved in Moodle |
| `/mod_form.php` | UI for test settings, insert validation if necessary|
| `/attempt.php` |  Extend $data_for_r_server |
| `/classes/local/attempt/attempt.php` |   Change url to R server `public function call_r_server`|
| `/classes/local/attempt/attempt.php` |Tag processing to be sent to R-Server: `public static function distribute_used_tags($tags, $itemsArray)` erweitern |
| `/lang/de/catadaptivequiz.php` <br>`/lang/en/catadaptivequiz.php` <br>...| Add translations/texts for test settings |


# R-Server

See README_RServer for more information on the setup of the RServer.

## Setup/Files
The R-Server is setup via Docker. 
[rstudio/plumber](https://www.rplumber.io/articles/hosting.html#docker) is used as image for the R-Server.

Dockerfile:
```Dockerfile
FROM rstudio/plumber

RUN apt-get update -qq && apt-get install -y \
	libssl-dev \
	libcurl4-gnutls-dev \
	r-base \
	r-base-core \
	r-base-dev \
	r-base-html \
	r-cran-boot \
	r-cran-class \
	r-cran-cluster \
	r-cran-codetools \
	r-cran-foreign \
	r-cran-kernsmooth \
	r-cran-lattice \
	r-cran-mass \
	r-cran-matrix \
	r-cran-mgcv \
	r-cran-nlme \
	r-cran-nnet \
	r-cran-rpart \
	r-cran-spatial \
	r-cran-survival \
	r-doc-html \
	r-recommended \
	r-cran-dplyr \
	r-cran-rjson \
	r-cran-jsonlite

	# Install plyr
RUN R -e "install.packages('plyr')" > /install_plyr.log

	# Install mirtCAT
RUN R -e "install.packages('mirtCAT')" > /install_mirtCAT.log
RUN R -e "install.packages('dplyr')" > /install_dplyr.log
RUN R -e "install.packages('rjson')" > /install_rjson.log
RUN R -e "install.packages('jsonlite')" > /install_jsonlite.log


# Expose port 80 for the web server
EXPOSE 80
```

A simple R-Script is used to start the webserver:
```R
library(plumber)
print(paste("getwd: ", getwd()))
pr <- plumber::plumb("./main-api.R")
pr$run(host = "0.0.0.0", port = 80)
```

In the main-api.R file the API is defined:
```R
# Another endpoint '/doCAT' is defined here, which executes the CAT functionality
# and returns a JSON object
#* @post /doCAT
function(data) {
	tryCatch({
		input_object <- data
		# do some checks/preprocessings with the input_object
		# ...

		# Call the test_script function with the input object
		json <- test_script(input_object)
		return (json)
	}, error = function(e) {
		# Handle error here
		message("An error occurred: ", e$message)
		return(NULL)
  })
}
```
## Restrictions
The R-Server is not able to access the Moodle Database. Therefore, the R-Server needs to get all necessary information via the REST-API.   
R is a single-threaded language. Therefore, the R-Server can only handle one request at a time. This can be a bottleneck if many users are using the Plugin at the same time.  
This can be solved by using a load balancer in front of the R-Server or by using a more powerful server or by using a different webserver that can handle multiple requests at the same time.

## REST-API
This section describes the REST-API of the R-Server. The R-Server is responsible for the calculation of the CAT-Logic. The Plugin communicates with the R-Server via a REST-API. The R-Server is a separate Server and can be updated without touching the Plugin.

### Input Data

```JSON
{
	"courseID": [
		"2"
	],
	"testID": [
		"2"
	],
	"itempool": {
		"items": [
			{
				"diff": [
					"0.57"
				],
				"content_area": [
					"I",
					"9",
					"25",
					"A",
					"KI"
				],
				"disc": [
					"1.52"
				],
				"cluster": "A",
				"enemys": [],
				"ID": "I0925AKa",
				"dbID": "67"
			},
			{
				"diff": [
					"-1.25"
				],
				"content_area": [
					"I",
					"9",
					"25",
					"W",
					"HI"
				],
				"disc": [
					"1.08"
				],
				"cluster": "A",
				"enemys": [],
				"ID": "I0925WHa",
				"dbID": "69"
			},
			...
		]
	},
	"settings": {
		"maxItems": [
			"12"
		],
		"minItems": [
			"1"
		],
		"minStdError": [
			"0.00000"
		],
		"criteria_not_adaptive": [
			"random"
		],
		"ncl_calib": [
			"2"
		],
		"ncl_link": [
			"2"
		],
		"ncl_adaptive": [
			"2"
		],
		"pers_est": [
			"MAP"
		],
		"criteria_adaptive": [
			"MI"
		],
		"exposure": {
			"enabled": [
				1
			],
			"nitems_exposure": [
				"4"
			]
		},
		"content_areas": {
			"enabled": [
				"1"
			],
			"distribution": [
				"catname8:0.5;catname2:0.3;catname3:0.5"
			]
		}
	},
	"person": {
		"personID": [
			"2"
		]
	},
	"test": {
		"itemID": [
			"U0206VHa",
			"U0206AHa"
		],
		"item": [ // indices of the items in the itempool
			27,
			26
		],
		"scoredResponse": [
			0,
			0
		],
		"itemtime": [
			21,
			4
		],
		"timeout": [
			false
		]
	}
}
```


### Output Data
``` JSON
{
	"personID":["2"], // user id
	"terminated":[false], // is quiz terminated
	"theta":[-1.3127], // ability measure
	"SE":[0.3918], // standarderror
	"nextItem":["B0720VHb"]  // next question id
}
```

### 





