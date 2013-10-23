Existing Events
=================================

### Actions

#####**vtiger.header**  
  Will be triggerd in the header of vtigerCRM

##### **vtiger.footer**  
  Will be triggerd in the footer of vtigerCRM

### Filters [$parameters]

#####**vtiger.filter.listview.querygenerator.before** [$queryGenerator]  
  Will be triggered before the QueryGenerator will be used to create the Query for the ListViewEntries  
  *Could be used to manipulate the QueryGenerator, used by the ListView*  

#####**vtiger.filter.listview.querygenerator.after** [$queryGenerator]  
  Will be triggered after the QueryGenerator was used to create the Query for the ListViewEntries  
  *Could be used to manipulate the QueryGenerator, used by the ListView*  

##### **vtiger.filter.listview.render** [$columnHtml, $row, $recordID]  
  
| $parameter        | Description           | 
| ------------- |-------------| 
| $columnHtml |html of the content of every column (Not the < td> itself)  |
| $row |complete Fetch of the Row from DB Query  |
| $recordID |associated recordID of this row  |

  Will be triggered at the end of every ListViewEntries generation and get the final HTML code of every column in one row  
  *Could be used to manipulate the HTML of each Column inside ListView*  
