#include "qfunc.c"
define MAXWC 100000

int main(){
  long visit,webcon,i;
  wccall=(char **)malloc(MAXWC*sizeof(char *));
  for(i=0;i<MAXWC;i++)wccall[i]=(char *)malloc(20*sizeof(char));
  readqrz("iu4ict",&visit,&webcon);
  printf("%ld\n%ld\n",visit,webcon);
  for(i=0;i<wcn;i++)printf("%ld:%s ",i,wccall[i]);
  printf("\n");
}
