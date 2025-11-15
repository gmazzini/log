#include "qfunc.c"

int main(){
  long visit,webcon;
  readqrz("iu4ict",&visit,&webcon);
  printf("%ld\n%ld\n",visit,webcon);
}
