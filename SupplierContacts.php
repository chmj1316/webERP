<?php
/* $Revision: 1.3 $ */

$PageSecurity=5;

include('includes/session.inc');

$title = _('Supplier Contacts');

include('includes/header.inc');


if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}

if (!isset($SupplierID)) {
	echo '<P><P>';
	prnMsg(_('This page must be called with the supplier code of the supplier for whom you wish to edit the contacts.') . '<BR>' . _('When the page is called from within the system this will always be the case.') .
			'<BR>' . _('Select a supplier first, then select the link to add/edit/delete contacts.'),'info');
	include('includes/footer.inc');
	exit;
}

if (isset($_GET['SelectedContact'])){
	$SelectedContact = $_GET['SelectedContact'];
} elseif (isset($_POST['SelectedContact'])){
	$SelectedContact = $_POST['SelectedContact'];
}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Contact']) == 0) {
		$InputError = 1;
		prnMsg(_('The contact name must be at least one character long'),'error');
	}


	if (isset($SelectedContact) AND $InputError != 1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$sql = "UPDATE SupplierContacts SET Position='" . $_POST['Position'] . "', Tel='" . $_POST['Tel'] .
				 "', Fax='" . $_POST['Fax'] . "', Email='" . $_POST['Email'] . "', Mobile = '". $_POST['Mobile'] .
				 "' WHERE Contact='$SelectedContact' AND SupplierID='$SupplierID'";

		$msg = _('The supplier contact information has been updated.');

	} elseif ($InputError != 1) {

	/*Selected contact is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new supplier  contacts form */

		$sql = "INSERT INTO SupplierContacts (SupplierID, Contact, Position, Tel, Fax, Email, Mobile) VALUES ('" .
				 $SupplierID . "', '" . $_POST['Contact'] . "', '" . $_POST['Position'] . "', '" . $_POST['Tel'] .
				 "', '" . $_POST['Fax'] . "', '" . $_POST['Email'] . "', '" . $_POST['Mobile'] . "')";

		$msg = _('The new supplier contact has been added to the database.');
	}
	//run the SQL from either of the above possibilites

	$ErrMsg = _('The supplier contact could not be inserted or updated because');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	prnMsg($msg,'success');

	unset($SelectedContact);
	unset($_POST['Contact']);
	unset($_POST['Position']);
	unset($_POST['Tel']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['Mobile']);

} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM SupplierContacts WHERE Contact='$SelectedContact' AND SupplierID = '$SupplierID'";

	$ErrMsg = _('The supplier contact could not be deleted because');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

	echo '<BR>' . _('Supplier contact has been deleted') . '<p>';

}


if (!isset($SelectedContact)){


	$sql = "SELECT Suppliers.SuppName, Contact, Position, Tel, Fax, Email
			  FROM SupplierContacts, Suppliers
			  WHERE SupplierContacts.SupplierID=Suppliers.SupplierID
			  AND SupplierContacts.SupplierID = '$SupplierID'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);

	if ($myrow) {
		echo '<B>' . _('Contacts Defined for') . " - $myrow[0]</B>";
	}

	echo "<TABLE BORDER=1>\n";
	echo "<TR><TD CLASS='tableheader'>" . _('Name') . "</TD>
			<TD CLASS='tableheader'>" . _('Position') . "</TD>
			<TD CLASS='tableheader'>" . _('Phone No') . "</TD>
			<TD CLASS='tableheader'>" . _('Fax No') . "</TD><TD CLASS='tableheader'>" . _('E-mail') .
			"</TD></TR>\n";

	do {
		printf("<TR><TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD><A HREF='mailto:%s'>%s</TD>
				<TD><A HREF='%s?SupplierID=%s&SelectedContact=%s'>" . _('Edit') . "</TD>
				<TD><A HREF='%s?SupplierID=%s&SelectedContact=%s&delete=yes'>" .  _('DELETE') . '</TD>
				</TR>',
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[5],
				$_SERVER['PHP_SELF'],
				$SupplierID,
				$myrow[1],
				$_SERVER['PHP_SELF'],
				$SupplierID,
				$myrow[1]);

	} while ($myrow = DB_fetch_row($result));

	//END WHILE LIST LOOP
}

//end of ifs and buts!

echo '</TABLE><P>';

if (isset($SelectedContact)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "SupplierID=$SupplierID" . "'>" .
		  _('Show all the supplier contacts for') . ' ' . $SupplierID . '</A></CENTER></P>';
}

if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedContact)) {
		//editing an existing branch

		$sql = "SELECT Contact, Position, Tel, Fax, Mobile, Email
				  FROM SupplierContacts
				  WHERE Contact='$SelectedContact'
				  AND SupplierID='$SupplierID'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Contact']  = $myrow['Contact'];
		$_POST['Position']  = $myrow['Position'];
		$_POST['Tel']  = $myrow['Tel'];
		$_POST['Fax']  = $myrow['Fax'];
		$_POST['Email']  = $myrow['Email'];
		$_POST['Mobile']  = $myrow['Mobile'];
		echo "<INPUT TYPE=HIDDEN NAME='SelectedContact' VALUE='" . $_POST['Contact'] . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='Contact' VALUE='" . $_POST['Contact'] . "'>";
		echo '<CENTER><TABLE><TR><TD>' . _('Contact') . ':</TD><TD>' . $_POST['Contact'] . '</TD></TR>';

	} else { //end of if $SelectedContact only do the else when a new record is being entered

		echo '<CENTER><TABLE><TR><TD>' . _('Contact Name') . ":</TD>
				<TD><INPUT TYPE='Text' NAME='Contact' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Contact'] . "'></TD></TR>";
	}

	echo "<INPUT TYPE=hidden NAME='SupplierID' VALUE='" . $SupplierID . "'>
		<TR><TD>" . _('Position') . ":</TD>
		<TD><INPUT TYPE=text NAME='Position' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Position'] . "'></TD></TR>
		<TR><TD>" . _('Telephone No') . ":</TD>
		<TD><INPUT TYPE=text NAME='Tel' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Tel'] . "'></TD></TR>
		<TR><TD>" . _('Facsimile No') . ":</TD>
		<TD><INPUT TYPE=text NAME='Fax' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Fax'] . "'></TD></TR>
		<TR><TD>" . _('Mobile No') . ":</TD>
		<TD><INPUT TYPE=text NAME='Mobile' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Mobile'] . "'></TD></TR>
		<TR><TD><A HREF='Mailto:" . $_POST['Email'] . "'>" . _('E-mail') . ":</A></TD>
		<TD><INPUT TYPE=text NAME='Email' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Email'] . "'></TD></TR>
		</TABLE>";

	echo "<CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Enter Information') . "'>";
	echo '</FORM>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
