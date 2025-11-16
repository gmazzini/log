#include "qfunc.c"
#define MAXWC 100000

int main(){
  long visited,i;
  int zz,c,webcon;
  char buf[1000],mycall[16];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;

  strcpy(mycall,"IK4LZH");
  wccall=(char **)malloc(MAXWC*sizeof(char *));
  for(i=0;i<MAXWC;i++)wccall[i]=(char *)malloc(20*sizeof(char));
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");


  zz=readqrz(mycall,&visited,&webcon);
  for(i=0;i<10;i++)printf("%ld %s\n",i,wccall[i]); exit(0);
  
  
  // insert all new call in the log not just in qrzwc
  sprintf(buf,"select distinct callsign from log where mycall='%s' and callsign not in (select callsign from qrzwebcontact where mycall='%s')",mycall,mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    sprintf(buf,"insert into qrzwebcontact (mycall,callsign,source) value ('%s','%s','me')",mycall,row[0]);
    // mysql_query(con,buf);
    printf("%s\n",buf);
  }
  mysql_free_result(res);

  // update all call in my log that was in qrcwc but not worked from me
  sprintf(buf,"select distinct callsign from log where mycall='%s' and callsign in (select callsign from qrzwebcontact where mycall='%s' and source!='me')",mycall,mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    sprintf(buf,"update qrzwebcontact set source='me' where mycall='%s' and callsign='%s'",mycall,row[0]);
    // mysql_query(con,buf);
    printf("%s\n",buf);
  }
  mysql_free_result(res);

  // chack on my wc and insert or update wc database
  zz=readqrz(mycall,&visited,&webcon);
  for(i=0;i<wcn;i++){
    sprintf(buf,"select count(*) from qrzwebcontact where mycall='%s' and callsign='%s'",mycall,wccall[i]);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    c=atoi(row[0]); mysql_free_result(res);
if(c==0)printf("--- %ld %s\n",i,wccall[i]);
    if(c==0)sprintf(buf,"insert into qrzwebcontact (mycall,callsign,source,sent,you) value ('%s','%s','oth',1,1)",mycall,wccall[i]);
    else sprintf(buf,"update qrzwebcontact set sent=1,you=1 where mycall='%s' and callsign='%s'",mycall,wccall[i]);
    // mysql_query(con,buf);
    printf("%s\n",buf);
  }
}
