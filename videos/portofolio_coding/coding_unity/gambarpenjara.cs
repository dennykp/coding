using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class gambarpenjara : MonoBehaviour {
	public PLAYER namavar;
	public GameObject showgambarpenjara;
	// Use this for initialization
	void Start () {
		
	}
	public void OnTriggerEnter(Collider collider){
		if (namavar.Counterpos == namavar.targetpos){
			if (collider.gameObject.name == "penjara") {
				uangplayerbaru.uangplayer -= 750;

				uangbankk.uangbank += 750;
			}
		}
}
	// Update is called once per frame
	void Update () {
		
	}
}
