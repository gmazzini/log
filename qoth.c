#include "qfunc.c"
#define MAXWC 100000

int main(){
  long visit,webcon,i,minlooked;
  int zz;
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
  for(;;){
    sprintf(buf,"select min(looked) from qrzwebcontact where mycall='%s'",mycall);
    mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
    minlooked=atol(row[0]); mysql_free_result(res);
    sprintf(buf,"select callsign from qrzwebcontact where mycall='%s' and looked=$minlooked order by rand()",mycall);
    mysql_query(con,buf);
    res=mysql_store_result(con);
    for(;;){
      row=mysql_fetch_row(res);
      if(row==NULL)break;
      zz=readqrz(row[0],&visit,&webcon);
      sleep(3+rand()%5);
      
    }
    mysql_free_result(res);
  }


  printf("%ld\n%ld\n",visit,webcon);
  for(i=0;i<wcn;i++)printf("%ld:%s ",i,wccall[i]);
  printf("\n");
}
