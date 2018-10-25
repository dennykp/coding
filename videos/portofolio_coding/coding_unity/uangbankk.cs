using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class uangbankk : MonoBehaviour {
	public static double uangbank;
 Text textbank;
	// Use this for initialization

	
	// Update is called once per frame

	void Awake(){
		textbank = GetComponent<Text> ();
		uangbank = 100000;
	}
	void Update () {
		textbank.text = "" + uangbank;
	}
}
