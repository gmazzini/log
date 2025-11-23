// plogin.c login check by GM @2025 V 2.0
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <mysql/mysql.h>
#include "/home/www/data/log.def"

int main(void){
  int c,vv,gg,mypage;
  char buf[1000],aux1[300],filter[50],tok[2][100];
  time_t epoch;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  const char charset[]="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  
  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<2)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';

  *aux1=*filter='\0';
  mypage=0;
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  epoch=time(NULL);
  sprintf(buf,"select count(*),mypage,filter from user where mycall='%s' and md5passwd='%s' limit 1",tok[0],tok[1]);
  mysql_query(con,buf); res=mysql_store_result(con); row=mysql_fetch_row(res);
  if(row==NULL || atoi(row[0])==0)strcpy(aux1,"");
  else {
    mypage=atoi(row[1]);
    strcpy(filter,row[2]);
    srand((unsigned int)epoch);
    gg=sizeof(charset)-1;
    for(c=0;c<16;c++)aux1[c]=charset[rand()%gg];
    aux1[16]='\0';
    sprintf(buf,"update user set ota='%s',lastota=%ld where mycall='%s' and md5passwd='%s'",aux1,epoch,tok[0],tok[1]);
    mysql_query(con,buf);
  }
  mysql_free_result(res);
  
  printf("Content-Type: text/plain\r\n\r\n");
  printf("%s,%d,%s\n",aux1,mypage,filter);
  mysql_close(con);
  return 0;
}
