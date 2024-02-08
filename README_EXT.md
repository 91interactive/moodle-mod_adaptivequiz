Following JSON should be the response of the R-Server. 
Values are examples to get the code working
--------------------------------------------------------------------
{
	"errormessage":null,  								// if an error occurs
	"nextdifficultylevel":3,							// next difficultyLevel
	"standarderror": '. rand(1,5) .',					// standard error
	"measure" : -5.65286,								// measure
	"id_next_question": '. rand(267,286) .',			// id of the next question
	"score": 3.4										// current calculated score (normally calculated at the end of the test)
}