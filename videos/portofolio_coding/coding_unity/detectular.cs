using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class detectular : MonoBehaviour {
	public PLAYER playerrr;
	// Use this for initialization
	void Start () {
		
	}
	public void OnTriggerEnter(Collider collider){
		if (playerrr.Counterpos == playerrr.targetpos) {
			if (collider.gameObject.name == "00") {
				playerrr.Redplayerpos += 1;
				playerrr.Counterpos++;
			}
		}
	}
	// Update is called once per frame
	void Update () {
		
	}
}
