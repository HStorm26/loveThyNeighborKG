<?php
// Author: Daniel Leaman
// purpose: to provide functions for talking to the db to get data for the LTN reports
// date: 2026 04 10
// notes: I hope to provide good comments to help you use the functions


// -----------------------
// include statement(s)
//-------------------------------
include_once("dbinfo.php");

// ---------------------------------
// formats:
// 1. $sd and $ed stand for start date and end dates and will be in the format YYYY-MM-DD to be compatible with html forms
// 
// ----------------------------------



// -----------------------------------------
// functions for unique volunteer count
// ------------------------------------------
function countUniqueVolunteersForDateRange($sd,$ed)
// returns an int
{

}
function volunteerUniqueEventsForDateRange($sd,$ed)
// returns a 2d array in the format [[string first, string last, int events partisipated in]...]
{

}
//---------------------------------------
// functions for inactive volunteers
// --------------------------------------
function getInactiveVolunteers()
// no input as it is allways to be one year in the past
// returns a 2d array in the format [[str first, str last, str date of last event (yyyy-mm-dd)]...]
{

}
// --------------------------------------
// functions for total volunteer hours
// ---------------------------------------

// use function roleHoursForDateRange($sd,$ed) in dbperson hours to break it down by role
// it uses the format [[$r(str role),$h(int hours),$m(int minutes)]....]

function totalHoursForDateRange($sd,$ed)
// retunrs array in format [int hours, int minutes]
{

}
// ------------------------------------------
// functions for hour category summary
// ------------------------------------------
function hoursPerRoleAllTime($roles)
// $roles is to be an array of the role ids as ints
// returns a 2d array in the format [[$r(str role),$h(int hours),$m(int minutes)]....]
{

}
