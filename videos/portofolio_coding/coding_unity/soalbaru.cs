using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class soalbaru : MonoBehaviour {
	public Text myText;
	public string[] animalDescriptions = 
	{
		"Description 1",
		"Description 2",
		"Description 3",
		"Description 4",
		"Description 5",
	};
	// Use this for initialization
	void Start () {
		string myString = animalDescriptions [Random.Range (0, animalDescriptions.Length)];
		myText.text = myString;
	}
	
	// Update is called once per frame
	void Update () {
		
	}
}
