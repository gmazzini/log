#include "qfunc.c"
#include "pfunc.c"
#define MAXWC 100000

int main(){
  long visited,i,entry,updated;
  int zz,c,webcon;
  char buf[10000],mycall[16],myemail[100],youremail[100];
  MYSQL *con;
  MYSQL_RES *res,*res1;
  MYSQL_ROW row,row1;

  strcpy(mycall,"IK4LZH");
  wccall=(char **)malloc(MAXWC*sizeof(char *));
  for(i=0;i<MAXWC;i++)wccall[i]=(char *)malloc(20*sizeof(char));
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_query(con,"SET time_zone='+00:00'");
  sprintf(buf,"select email from who where callsign='%s'",mycall);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res); strcpy(myemail,row[0]); mysql_free_result(res);

goto next;
  
  printf(">> Insert all new call in the log not just in qrzwc\n");
  sprintf(buf,"select distinct callsign from log where mycall='%s' and callsign not in (select callsign from qrzwebcontact where mycall='%s')",mycall,mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(entry=0;;entry++){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    sprintf(buf,"insert into qrzwebcontact (mycall,callsign,source) value ('%s','%s','me')",mycall,row[0]);
    mysql_query(con,buf);
    printf("%s\n",buf);
  }
  mysql_free_result(res);
  printf("--- %ld entries\n\n",entry);

  printf(">> Update all call in my log that was in qrcwc but not worked from me\n");
  sprintf(buf,"select distinct callsign from log where mycall='%s' and callsign in (select callsign from qrzwebcontact where mycall='%s' and source!='me')",mycall,mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(entry=0;;entry++){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    sprintf(buf,"update qrzwebcontact set source='me' where mycall='%s' and callsign='%s'",mycall,row[0]);
    mysql_query(con,buf);
    printf("%s\n",buf);
  }
  mysql_free_result(res);
  printf("--- %ld entries\n\n",entry);

  printf(">> Check on my wc and insert or update wc database\n");
  zz=readqrz(mycall,&visited,&webcon);
  for(entry=i=0;i<wcn;i++){
    sprintf(buf,"select count(*) from qrzwebcontact where mycall='%s' and callsign='%s'",mycall,wccall[i]);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res); c=atoi(row[0]); mysql_free_result(res);
    if(c==0){
      sprintf(buf,"insert into qrzwebcontact (mycall,callsign,source,sent,you) value ('%s','%s','oth',1,1)",mycall,wccall[i]);
      entry++;
    }
    else sprintf(buf,"update qrzwebcontact set sent=1,you=1 where mycall='%s' and callsign='%s'",mycall,wccall[i]);
    mysql_query(con,buf);
  }
  printf("--- %ld entries with %ld inserted\n\n",wcn,entry);

  printf(">> Set me on unset wc\n");
  sprintf(buf,"select callsign from qrzwebcontact where mycall='%s' and looked>0 and me=0 and you=1 and Ewc=1 order by Nwc desc",mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(updated=entry=0;;entry++){
    sleep(10+rand()%10);
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    if(setqrz(row[0])==0)continue;
    sprintf(buf,"update qrzwebcontact set me=1 where mycall='%s' and callsign='%s'",mycall,row[0]);
    mysql_query(con,buf);
    printf("%s\n",buf);
    updated++;
  }
  mysql_free_result(res);
  printf("--- %ld entries with %ld updated\n\n",entry,updated);

  printf(">> Send email to request wc at station just contacted\n");
  sprintf(buf,"select callsign from qrzwebcontact where mycall='%s' and sent=0 and qrzed=0 and source='me' order by rand()",mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(updated=entry=0;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    c=qrzcom(con,row[0]);
    sprintf(buf,"update qrzwebcontact set qrzed=%ld where mycall='%s' and callsign='%s'",time(NULL)/86400,mycall,row[0]);
    mysql_query(con,buf);
    printf("%s\n",buf);
    entry++;
    if(c==0)continue;
    sprintf(buf,"select email from who where callsign='%s'",row[0]);
    mysql_query(con,buf); res1=mysql_store_result(con); row1=mysql_fetch_row(res1); 
    if(row1!=NULL)strcpy(youremail,row1[0]); else *youremail='\0'; 
    mysql_free_result(res1);
    sprintf(buf,"select count(email) from qrzwebcontact_email where email='%s'",youremail);
    mysql_query(con,buf); res1=mysql_store_result(con); row1=mysql_fetch_row(res1); c=atoi(row1[0]); mysql_free_result(res1);
    sleep(3+rand()%5);
    if(strlen(youremail)>5 && c==0){
      sprintf(buf,"Hi %s,<br><br> in the past, we have connected and indeed, you are in my log. I noticed that you also have a profile on qrz.com, and I do too. It would really make me happy if you could add your callsign to my qrz.com page called \"Web Contacts,\" where I am collecting a large number of friends. If you decide to proceed, you can: <ul><li>1. log in to the qrz.com website <a href=\"https://www.qrz.com/\"> https://www.qrz.com/</a> with your credentials,</li><li>2. search for my callsign by typing %s or by clicking the link <a href=\"https://www.qrz.com/lookup/%s\">https://www.qrz.com/lookup/%s</a></li><li>3. click on the tab labeled \"Web\",</li><li>4. go to the box labeled \"Add your Web Contact\", and click on the button that says \"DE %s\"</li></ul><br><br>Thank you very much, and I hope to connect with you again soon.<br><br>73 de %s",row[0],mycall,mycall,mycall,row[0],mycall);
      myemailsend(myemail,youremail,"QRZ Web Contacts request",buf);
      sprintf(buf,"update qrzwebcontact set sent=1 where mycall='%s' and callsign='%s'",mycall,row[0]);
      mysql_query(con,buf);
      printf("%s\n",buf);
      sprintf(buf,"insert ignore into qrzwebcontact_email (email) values ('%s')",youremail);
      mysql_query(con,buf);
      printf("%s\n",buf);
      updated++;
      sleep(30);
    }
  }
  mysql_free_result(res);
  printf("--- %ld entries found with %ld email sent\n\n",entry,updated);

  next:
  printf(">> Send email to ask for wc at station with wc but never contacted\n");
  sprintf(buf,"select callsign,Nwc from qrzwebcontact where mycall='%s' and sent=0 and qrzed=0 and source='oth' and me=0 and you=0 and Ewc=1 order by Nwc desc",mycall);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(updated=entry=0;;){
    sleep(3+rand()%5);
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    if(setqrz(row[0])==0)continue;
    entry++;
    sprintf(buf,"update qrzwebcontact set me=1,qrzed=%ld where mycall='%s' and callsign='%s'",time(NULL)/86400,mycall,row[0]);
    mysql_query(con,buf);
    printf("%s\n",buf);
    sleep(3+rand()%5);
    c=qrzcom(con,row[0]);
    if(c==0)continue;
    sprintf(buf,"select email from who where callsign='%s'",row[0]);
    mysql_query(con,buf); res1=mysql_store_result(con); row1=mysql_fetch_row(res1); 
    if(row1!=NULL)strcpy(youremail,row1[0]); else *youremail='\0'; 
    mysql_free_result(res1);
    sprintf(buf,"select count(email) from qrzwebcontact_email where email='%s'",youremail);
    mysql_query(con,buf); res1=mysql_store_result(con); row1=mysql_fetch_row(res1); c=atoi(row1[0]); mysql_free_result(res1);
    if(strlen(youremail)>5 && c==0){
      sprintf(buf,"Hi %s,<br><br>I noticed that in your profile on qrz.com you have enabled \"Web Contacts\" and have collected %ld entries, when I visited you. I have also added my callsign to your list with great pleasure. It would really make me happy if you could also add your callsign to my qrz.com page \"Web Contacts,\" where I am collecting a large number of friends. If you decide to proceed, you can: <ul><li>1. log in to the qrz.com website <a href=\"https://www.qrz.com/\">https://www.qrz.com/</a> with your credentials,</li><li>2. search for my callsign by typing %s or by clicking the link <a href=\"https://www.qrz.com/lookup/%s\"> https://www.qrz.com/lookup/%s</a></li><li>3. click on the tab labeled \"Web\",</li><li>4. go to the box labeled \"Add your Web Contact\", and click on the button that says \"DE %s\"</li></ul><br><br>Thank you very much, and I hope to connect with you again soon.<br><br> 73 de %s",row[0],atol(row[1]),mycall,mycall,mycall,row[0],mycall);
      myemailsend(myemail,youremail,"QRZ Web Contacts",buf);
      sprintf(buf,"update qrzwebcontact set sent=1 where mycall='%s' and callsign='%s'",mycall,row[0]);
      mysql_query(con,buf);
      printf("%s\n",buf);
      sprintf(buf,"insert ignore into qrzwebcontact_email (email) values ('%s')",youremail);
      mysql_query(con,buf);
      printf("%s\n",buf);
      updated++;
      sleep(30);  
    }    
  }
  mysql_free_result(res);
  printf("--- %ld set attemped with %ld email sent\n\n",entry,updated);

  
  printf("DONE\n");
}
