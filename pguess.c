// pguess.c callsign guess processor by GM @2025 V 2.0
#include "pfunc.c"
#include "/home/www/data/log.def"

int main(void) {
  int c,gg;
  char in[20];
  MYSQL *con;
  MYSQL_RES *res;
  MYSQL_ROW row;

  for(gg=0;;){
    c=getchar();
    if(c==EOF)break;
    in[gg++]=(char)c;
  }
  in[gg]='\0';

  printf("Content-Type: text/plain\r\n\r\n");
  con=mysql_init(NULL);
  if(con==NULL)exit(1);
  if(mysql_real_connect(con,dbhost,dbuser,dbpassword,dbname,0,NULL,0)==NULL)exit(1);

printf("%s\n",in);

  
  mysql_close(con);
}
