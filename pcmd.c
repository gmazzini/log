// pcmd.c command processor by GM @2025 V 2.0
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <mysql/mysql.h>

int main(void) {
  int c,vv,gg;
  char buf[1000],aux1[300],tok[2][100];

  for(vv=0,gg=0;;){
    c=getchar();
    if(c==EOF)break;
    if(c==','){tok[vv][gg]='\0'; vv++; gg=0; continue;}
    if(vv<2)tok[vv][gg++]=(char)c;
  }
  tok[vv][gg]='\0';
}
