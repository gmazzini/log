// pguess.c callsign guess processor by GM @2025 V 2.0
#include "pfunc.c"
#include "/home/www/data/log.def"

int main(void) {
  int c,gg;
  char in[20],buf[2000];
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
  sprintf(buf,"SET @in = '%s'; SELECT callsign, common FROM (SELECT a.callsign, COUNT(*) AS common FROM aux3 a WHERE CHAR_LENGTH(@in) >= 3 AND a.gram IN (SELECT DISTINCT SUBSTRING(@in, n.n, 3) FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) n WHERE n.n <= CHAR_LENGTH(@in) - 2) GROUP BY a.callsign UNION ALL SELECT a.callsign, COUNT(*) AS common FROM aux2 a WHERE CHAR_LENGTH(@in) = 2 AND a.gram = @in GROUP BY a.callsign ) x ORDER BY common DESC, callsign LIMIT 200;",in);
  mysql_query(con,buf);
  res=mysql_store_result(con);
  for(;;){
    row=mysql_fetch_row(res);
    if(row==NULL)break;
    printf("%s ",row[0]);
  }
  mysql_free_result(res);
  printf("\n");
  mysql_close(con);
}
