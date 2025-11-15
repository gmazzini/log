#include "qfunc.c"
define MAXWC 100000

int main(){
  long visit,webcon;
  readqrz("ik4lzh",&visit,&webcon);
  printf("%ld\n%ld\n",visit,webcon);
}
