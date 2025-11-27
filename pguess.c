// pguess.c callsign guess processor by GM @2025 V 2.0
#include "pfunc.c"
#include "/home/www/data/log.def"

int main(void) {
  int c,vv,gg;
  char buf[1000],tok[4][100],mycall[16],*p;
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;
  time_t epoch;

  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<4)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';
  
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);
  mysql_close(con);
}
