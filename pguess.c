// pguess.c callsign guess processor by GM @2025 V 2.0
#include "pfunc.c"
#include "/home/www/data/log.def"

#define MAXL 16
static int prev[MAXL+1];
static int curr[MAXL+1];

static int min3(int a,int b,int c){
  int m;
  m=a;
  if(b<m)m=b;
  if(c<m)m=c;
  return m;
}

int levenshtein(char *s,char *t){
  int n,m,i,j,cost;
  char si;
  n=strlen(s); m=strlen(t);
  if(n>MAXL)n=MAXL;
  if(m>MAXL)m=MAXL;
  if(n==0)return m;
  if(m==0)return n;
  for(j=0;j<=m;j++)prev[j]=j;
  for(i=1;i<=n;i++){
    curr[0]=i;
    si=s[i-1];
    for(j=1;j<=m;j++){
      cost=(si==t[j-1])?0:1;
      curr[j]=min3(prev[j]+1,curr[j-1]+1,prev[j-1]+cost);
    }
    for(j=0;j<=m;j++)prev[j]=curr[j];
  }
  return prev[m];
}

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
  sprintf(buf,"SELECT callsign, SUM(common) AS common FROM (SELECT a.callsign, COUNT(*) AS common FROM aux3 a WHERE %d >= 3 AND a.gram IN (SELECT DISTINCT SUBSTRING('%s', n.n, 3) FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) n WHERE n.n <= %d - 2) GROUP BY a.callsign UNION ALL SELECT a.callsign, COUNT(*) AS common FROM aux2 a WHERE %d >= 3 AND a.gram IN (SELECT DISTINCT SUBSTRING('%s', n.n, 2) FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) n WHERE n.n <= %d - 1) GROUP BY a.callsign UNION ALL SELECT a.callsign, COUNT(*) AS common FROM aux2 a WHERE %d = 2 AND a.gram = '%s' GROUP BY a.callsign) x GROUP BY callsign ORDER BY common DESC, callsign LIMIT 300;",gg,in,gg,gg,in,gg,gg,in);
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
