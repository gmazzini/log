#include "qfunc.c"

int main(){
  long visit,webcon;
  readqrz("iz4cow",&visit,&webcon);
  printf("%ld\n%ld\n",visit,webcon);
}
