<?php


interface  LogootPlus {

/*
 * function that must create the patch with the undo operation
 * with the $patchid patch in the operation undo 
 * function undo($patchid)
 * 
 * 
 * 
 *    function deliver(patch):
     for op in patch do
       switch (op)
       case Insert (id, content, S):
            dvisibilite := cemetery.get(id) + 1;
            if (dvisibilite = 1) then
               position := idTable.binarySearch(id);
               document.insert(position, content);
               idT able.insert(position, id);
            else
               cemetery.set(id, dvisibilite);
             fi
       case Delete(id, content, S):
            position := idTable.binarySearch(id);
            if (IdT able[position] = id) then
                document.remove(position, content);
                idT able.remove(position, content);
                dvisibilite := 0;
            else
                dvisibilite := cemetery.get(id) − 1;
            fi
            cemetery.set(id, dvisibilite)
end;
         done
 end;


 * 
 * 
 */

	
	/*
	 *
                                         function redo(op)
                                       1  op.degree++
                                       2  if ( op.degree = 1 )
                                       3    if ( op = Annuler(op , S) )
                                       4     undo(op )
                                       5  else
                                       6     execute(//op)
                                       7  for opi .Sem(op, opi )
                                       8  redo(opi )
                                       9

	 */
	
	
	/*
  function undo(op)
1 op.degree−−
2 	if ( op.degree = 0 )
3 		if ( op = Annuler(op , S) )
4 		redo(op )
5 	else
6 	for opi s.t. Sem(op, opi )
7      undo(opi )
8 execute(/op)
9

	 */
}

?>