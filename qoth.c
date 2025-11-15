#include "qfunc.c"
#define MAXWC 100000

int main(){
  long visited,webcon,i,minlooked,tt;
  int zz,c;
  char buf[1000],mycall[16];
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
  for(;;){
    sprintf(buf,"select min(looked) from qrzwebcontact where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    minlooked=atol(row[0]); mysql_free_result(res);
    sprintf(buf,"select callsign from qrzwebcontact where mycall='%s' and looked=%ld order by rand()",mycall,minlooked);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      zz=readqrz(row[0],&visited,&webcon);
      tt=time(NULL)/86400;
      sprintf(buf,"update qrzwebcontact set looked=%d,visited=%ld,Ewc=%ld,Nwc=%d where mycall='%s' and callsign='%s'",tt,visited,webcon,wcn,mycall,row[0]);
printf("1> %s\n",buf);

      sleep(3+rand()%5);
      if(zz==0)continue;
      for(i=0;i<wcn;i++){
        if(strcmp(wccall[i],mycall)==0){
          sprintf(buf,"update qrzwebcontact set me=1 where mycall='%s' and callsign='%s'",mycall,row[0]);
printf("2> %s\n",buf);
        }
        sprintf(buf,"select count(*) from qrzwebcontact where mycall='%s' and callsign='%s'",mycall,wccall[i]);
        mysql_query(con,buf); res1=mysql_store_result(con); row1=mysql_fetch_row(res1);
        c=atoi(row1[0]); mysql_free_result(res1);
        if(c==0){
          sprintf(buf,"insert into qrzwebcontact (mycall,callsign,source) value ('%s','%s','oth')",mycall,wccall[i]);
printf("3> %s\n",buf);

        }

      }
      
      
    }
    mysql_free_result(res);
  }


  printf("\n");
}
